
#  OccasionGenius

The _unofficial_ OccasionGenius WP plugin allows you to easily output a beautiful and simple events page without any coding using the [OccasionGenius](https://occasiongenius.com/) API.

##  Installation

Download and active the plugin, then go to "Settings" => "OccasionGenius." From there, input your API key and save your settings (along with any design tweaks).

After this has been completed, you can add to an "Events" (/events/) page via either of the follow:

1.  Shortcode: Add the `[occassiongenius_events]` Shortcode to the page.
2.  Blocks: Add the `OccasionGenius Events` Block via Gutenberg.

[![OccasionGenius](/public/images/v0_7_0_1.png?raw=true)]
[![OccasionGenius](/public/images/v0_7_0_2.png?raw=true)]

##  Carbon Fields / Options
*  og-token-key
*  og-time-format
*  og-time-zone
*  og-disabled-flags
*  og-disabled-areas
*  og-featured-flags
*  og-design-per-page-limit
*  og-design-header-image-1
*  og-design-header-image-2
*  og-design-header-image-3
*  og-design-heading
*  og-design-subheading
*  og-design-hp-btn-text
*  og-design-hp-btn-url
*  og-developer-security-key

##  Post Types
*  og_events

###  Fields
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

##  API Routes

###  All Events
`/wp-json/occasiongenius/v1/events`

###  All Flags (Categories)
`/wp-json/occasiongenius/v1/flags`

###  Single Flag (Category)
`/wp-json/occasiongenius/v1/flag/[flag_id]?limit=X&page=X`

###  Single Event
`/wp-json/occasiongenius/v1/event/[slugline]`

###  Single Venue
`/wp-json/occasiongenius/v1/venue/[uuid]`

##  React Setup

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
- Pages/
--  Categories.js
--  EventDetails.js
--  Events.js
--  Home.js
--  SingleCategory.js
--  SingleVenue.js

##  Coming Soon / Todo's

*  Replace `CURL` w/ `wp_remote_get` (v0.7.1)

*  Search and Filter Components (v0.8.x)

*  Automated Sitemap Injection (v0.8.x)

*  Event Schema Implementation (v0.9.x)

*  Administration Panel SEO Options (Title's & Descriptions) + Front End SEO Tweaks (v0.9.x)

*  Google Maps API Key, Output, and Get Directions  (v0.8.x)

*  Popularity Score Queries/Sorting

*  API Caching & Faster Performance / Responses  (v0.8.x)

*  Past Events Logic (Error Notice / View All Events)  (v0.8.x)

*  User Personalization & localStorage  (v0.8.x / v0.9.x)

*  Recommendation Logic (other events on this day, in the area, and personalization)

##  Known Bugs/Issues

*  Responsive Cleanup w/ Tailwind

*  Calendar Date Format (Google Calendar)

*  Next/Max Page Conditional