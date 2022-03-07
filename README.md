# Occasion Genius

The OccasionGenius WP plugin allows you to easily output a beautiful and simple events page without any coding using the [OccasionGenius](https://occasiongenius.com/) API.

## Installation

Download and active the plugin, then go to "Settings" => "OccasionGenius."  From there, input your API key and save your settings.
After this has been completed, you can add to an "Events" (/events/) page via either of the follow:

1.  Shortcode:  Add the `[occassiongenius_events]` Shortcode to the page.
2.  Blocks:  Add the `OccasionGenius Events` Block via Gutenberg.

## Carbon Fields / Options

* og-token-key
* og-time-format
* og-time-zone
* og-disabled-flags
* og-disabled-areas
* og-design-per-page-limit
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

### Single Event

/wp-json/occasiongenius/v1/event/[slugline]

## CSS Overrides

* .occasiongenius-parent-container
* .occassiongenius-loaded
* .occasiongenius-container
* .occasiongenius-pagination
* .occasiongenius-pagination button
* .occasiongenius-single-item 
* .occasiongenius-tile
* .occasiongenius-single_image
* .occasiongenius-event-location
* .occasiongenius-single_title
* .occasiongenius-single_location
* .occasiongenius-single_location
* .occasiongenius-event-location-right
* .occasiongenius-single_date_day
* .occasiongenius-single_date_month
* .occassiongenius-single-venue-get-tickets
* .occassiongenius-single-mapping
* .occasiongenius-single-page
* .return_to_all_events
* .occassiongenius-single-page-image
* .occassiongenius-single-title
* .occassiongenius-single-description
* .occassiongenius-single-times
* .occassiongenius-single-venue-information
* .occassiongenius-single-venue-information-title

## Coming Soon

* Custom URL's (versus required `/events/` URL)
* Event Schema
* Search & Filters
* Cleaner URL Routing (w/ Browser History)
* Automatic Sitemap Injection
* API Caching & Faster Performance / Responses