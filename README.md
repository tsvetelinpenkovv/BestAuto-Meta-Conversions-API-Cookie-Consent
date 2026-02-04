# BestAuto Meta Conversions API + Cookie Consent

The **BestAuto Meta Conversions API** module is a professional integration for PrestaShop 1.7.7.5 designed to bridge the gap between client-side tracking and server-side reliability. By leveraging the **Meta Conversions API (CAPI)**, this module ensures that critical marketing data is captured even when browser-based tracking is restricted by ad-blockers or privacy settings.

### Core Functionality and Integration

The module operates by intercepting key user actions on the server side and transmitting them directly to Meta's Graph API. A primary focus of this development is the seamless synchronization with the existing **Facebook Pixel** installation. Through a robust **event deduplication** mechanism, the module generates unique event identifiers that are shared between the server-side CAPI calls and the client-side Pixel scripts, ensuring that Meta's systems can accurately merge these data streams without inflating conversion counts.

### GDPR Compliance and Consent Management

In strict adherence to European data protection regulations, the module integrates directly with the **uecookie** consent management system. Data transmission to Meta is conditionally executed only when the user has explicitly granted marketing consent. The following table outlines the event tracking logic and the associated triggers within the PrestaShop environment:

| Event Name | Trigger Mechanism | Data Points Included |
| :--- | :--- | :--- |
| **ViewContent** | Product page initialization | Product ID, Price, Currency, Hashed User Data |
| **AddToCart** | `actionProductAdd` hook | Product ID, Price, Currency, FBP/FBC Cookies |
| **InitiateCheckout** | Checkout step 1 detection | Cart Total, Currency, Client IP, User Agent |
| **Purchase** | `actionValidateOrder` hook | Order ID, Total Value, Hashed Email/Phone |

### Technical Specifications and Configuration

The module provides a comprehensive administration interface for managing integration parameters. Users can configure their **Pixel ID**, **Access Token**, and **Test Event Code** to verify the integration within the Meta Events Manager. Furthermore, a built-in logging system located at `modules/bestautocapi/logs/capi.log` provides detailed insights into every API request and response, facilitating rapid troubleshooting and verification of successful data transmission.

### Installation Requirements

To ensure optimal performance, the module requires the `uecookie` module to be active for consent verification. Installation is performed through the standard PrestaShop module manager by uploading the provided package. Once installed, the module automatically registers necessary hooks and initializes the configuration schema.

***

**Developer:** Tsvetelin Penkov  
**Version:** 1.0.0  
**Compatibility:** PrestaShop 1.7.x - 8.x
