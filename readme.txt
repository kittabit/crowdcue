=== Crowdcue ===
Contributors: kittabit
Donate link:
Tags: events, calendar, event, schedule, organizer, venue
Requires at least: 5.4
Tested up to: 6.0
Stable tag: 1.3.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Crowdcue is the _unofficial_ OccasionGenius WordPress plugin allows you to easily output a beautiful and simple events page without any coding using the [OccasionGenius](https://occasiongenius.com/) API.

== Installation ==

Download and active the plugin, then go to "Settings" => "Crowdcue." From there, input your OccasionGenius API key and save your settings (along with any design tweaks).

After this has been completed, you can add to an "Events" (/events/) page via either of the follow:

1.  Shortcode: Add the `[occasiongenius_events]` Shortcode to the page.
2.  Blocks: Add the `OccasionGenius Events` Block via Gutenberg.

== Carbon Fields / Options ==

*  og-token-key
*  og-time-format
*  og-time-zone
*  og-disabled-flags
*  og-disabled-areas
*  og-featured-flags
*  og-google-maps-api-key
*  og-design-per-page-limit
*  og-design-header-image-1
*  og-design-header-image-2
*  og-design-header-image-3
*  og-design-heading
*  og-design-subheading
*  og-design-hp-btn-text
*  og-design-hp-btn-url
*  og-developer-security-key
*  og-analytics-ua-id

== Post Types ==
*  og_events

== Fields ==
*  og-event-name
*  og-event-uuid
*  og-event-popularity-score
*  og-event-description
*  og-event-flags
*  og-event-start-date
*  og-event-end-date
*  og-event-start-date-unix
*  og-event-end-date-unix
*  og-event-event-dates
*  og-event-source-url
*  og-event-image-url
*  og-event-ticket-url
*  og-event-venue-name
*  og-event-venue-uuid
*  og-event-venue-address-1
*  og-event-venue-address-2
*  og-event-venue-city
*  og-event-venue-state
*  og-event-venue-zip-code
*  og-event-venue-country
*  og-event-venue-latitude
*  og-event-venue-longitude


== API Routes ==

###  All Events

`/wp-json/occasiongenius/v1/events`

###  All Flags (Categories)

`/wp-json/occasiongenius/v1/flags`

###  All Areas

`/wp-json/occasiongenius/v1/areas`

###  Single Flag (Category)

`/wp-json/occasiongenius/v1/flag/[flag_id]?limit=X&page=X`

###  Single Event

`/wp-json/occasiongenius/v1/event/[slugline]`

###  Single Venue

`/wp-json/occasiongenius/v1/venue/[uuid]`

###  Nearby Locations / Events

`/wp-json/occasiongenius/v1/nearby/[uuid]`


== React Setup ==


- Layout.js
- index.js
- Components/
--  Breadcrumbs.js
--  CategoryOutput.js
--  EventCategorySmall.js
--  EventGridItem.js
--  EventSingle.js
--  Header.js
--  Loading.js
--  RelatedEvents.js
--  VenueOutput.js
--  UpcomingForYou.js
--  Components/
----  OGUserLogging.js
- Pages/
--  Categories.js
--  EventDetails.js
--  Events.js
--  Home.js
--  SingleCategory.js
--  SingleVenue.js
--  ForYou.js

== CSS Overrides ==

### Global
* og-master-container

### Home
* og-header-container   
* og-header-container-outer    
* og-header-container-inner-inner 
* og-header-container-inner-right
* og-header-container-inner-right-h1
* og-header-container-inner-right-p
* og-header-container-inner-left
* og-home-view-all-buttons
* og-home-view-all-categories 
* og-home-view-all-events

== Coming Soon / Todo's ==

*  Popularity Score Queries/Sorting

*  Recommendation Logic (other events on this day, in the area, and personalization)

*  Pre-Defined Cookies / LocalStorage (for inner-events queries - such as booking dates)