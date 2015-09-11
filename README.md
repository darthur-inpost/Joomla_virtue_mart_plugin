# Joomla_virtue_mart_plugin
For Joomla! InPost UK have created a Virtue Mart plugin.

## Installation

### Find the New Plugin

The code that is in the folders needs to be copied into the Joomla! installation root folder. Then you will need to go the Extension Manager. From there go to Discover. It should then find the InPost Shipping method for VirtueMart. Then you will need to Install the shipping method. Then you will need to go to the Plugin Manager. If you either pick the Type as "vmshipment" or filter on VM Shipment you will find the new plugin.

### Switch on the New Plugin

The new plugin requires switching on. You will simply need to Enable it.

### Configure the Plugin

There are several options that need to be configured within VirtueMart before the shipping method is available on the checkout of the shop.

You will need to add a new Shipping method under Components -> VirtueMart -> Shipping Mwethods

* Shipment Name : InPost
* Sef Alias : inpost
* Published : Yes
* Shipment Description : Via InPost Lockers
* Shipment Method : VM Shipment - InPost Parcels
* Shopper Group : "Available for all"
* List Order : As you would like

Then you need to fill in the Configuration details.

#### Configure the Shipping Method

There are some configuration options that need to be setup.

* Logo : Use Default
* Show on productdetails : Yes
* Api url : The one provided to you
* Api key : The one provided to you
* Weight unit : Kilogramme (if your product weights are in grams use Gramme)
* Max weight : 15 (kg or 15000 grams)
* Max dimension A : 8x38x64
* Max dimension B : 19x38x64
* Max dimension C : 38x38x64
* Allowed Country : "Available for all"
* Shop cities : Fill only if you want to restrict this
* Shipment Cost : Extra cost for the shipping method
* Package Fee : How much to charge
* Tax : Whichever Tax method is required
* Minimum Amount for Free Shipment : If the user can select the shipping method for free set ths value.

Don't forget to **save** before leaving this screen.

