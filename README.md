# OccasionGenius

The OccasionGenius WP plugin allows you to easily output a beautiful and simple events page without any coding using the [OccasionGenius](https://occasiongenius.com/) API.

## Installation

Download and active the plugin, then go to "Settings" => "OccasionGenius."  From there, input your API key and save your settings (along with any design tweaks).
After this has been completed, you can add to an "Events" (/events/) page via either of the follow:

1.  Shortcode:  Add the `[occassiongenius_events]` Shortcode to the page.
2.  Blocks:  Add the `OccasionGenius Events` Block via Gutenberg.

[![OccasionGenius](/public/images/v0_6_0_screenshot_1.png?raw=true)](#features)

## Carbon Fields / Options

* og-token-key
* og-time-format
* og-time-zone
* og-disabled-flags
* og-disabled-areas
* og-featured-flags
* og-design-per-page-limit
* og-design-primary-btn-color
* og-design-primary-btn-weight
* og-design-secondary-btn-color
* og-design-secondary-btn-weight
* og-design-primary-event-color
* og-design-primary-event-weight
* og-design-primary-font-family
* og-design-header-image-1
* og-design-header-image-2
* og-design-header-image-3
* og-design-heading
* og-design-subheading
* og-design-hp-btn-text
* og-design-hp-btn-url
* og-developer-security-key

## Post Types
* og_events

### Fields
* og-event-name
* og-event-uuid
* og-event-popularity-score
* og-event-description
* og-event-flags
* og-event-start-date
* og-event-end-date
* og-event-start-date-unix
* og-event-end-date-unix
* og-event-event-dates
* og-event-source-url
* og-event-image-url
* og-event-ticket-url
* og-event-venue-name
* og-event-venue-uuid
* og-event-venue-address-1
* og-event-venue-address-2
* og-event-venue-city
* og-event-venue-state
* og-event-venue-zip-code
* og-event-venue-country
* og-event-venue-latitude
* og-event-venue-longitude

## API Routes

### All Events

/wp-json/occasiongenius/v1/events

### Single Flag (Category)

/wp-json/occasiongenius/v1/flag/[flag_id]

### Single Event

/wp-json/occasiongenius/v1/event/[slugline]

## Coming Soon / Todo's

* /events/categories (v0.7.0)
* /events/all (v0.7.0)
* Replace `CURL` w/ `wp_remote_get` (v0.7.0)
* Search and Filter Components
* Go Back / Hisory Updates (Browser Links)
* Venue Specific URL's
* Category/Flag Specific URL's
* Automated Sitemap Injection
* Event Schema Implementation
* Administration Panel SEO Options (Title's & Descriptions)
* Google Maps API Key, Output, and Get Directions
* Popularity Score Queries/Sorting
* Social Sharing + Add To Calendar
* API Caching & Faster Performance / Responses
* Past Events Logic (Error Notice / View All Events)
* User Personalization & localStorage
* Recommendation Logic (other events on this day, in the area, and personalization)
