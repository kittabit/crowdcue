import React from 'react';
import { Link } from "react-router-dom";
class EventSingle extends React.Component {

    constructor (props){

        super(props);
        this.state = {
          event: [],
          isLoading: 1,
          events_url: "/wp-json/occasiongenius/v1/event/",
          loadingText: "Loading Event Data"
        }
        
    }

    componentDidMount() {

        const event_url = this.state.events_url + this.props.uuid;

        Promise.all([
            fetch(event_url)
        ])      
        .then(([res]) => Promise.all([res.json()]))
        .then(([data]) => this.setState({            
            event: data.event,
            isLoading: 0
        }));

    }

    handleEvent(){
    
        console.log(this.props);  
    
    }    

    render() {

        return ( 
            <>
                {(() => {
                    if (this.state.isLoading) {
                        return (
                            <div className="occassiongenius-loaded">Loading Event Details...</div>
                        )
                    } else {
                        return (

                            <div className="occasiongenius-single-page" data-popularity={ this.state.event.popularity_score }>
                                
                                <Link to="/events/" className="return_to_all_events">&lt; Return to All Events</Link>

                                <div className="occassiongenius-single-page-image" style={{ backgroundImage: `url(${this.state.event.image_url})` }}>
                                    <img src={this.state.event.image_url} alt={this.state.event.name} />
                                </div>

                                <h1 className="occassiongenius-single-title" data-uuid={this.props.uuid}>{ this.state.event.name }</h1>
                                <div className="occassiongenius-single-description">{ this.state.event.description }</div>

                                <div className="occassiongenius-single-times"> 
                                    <span>
                                    { this.state.event.start_date } 
                                    {this.state.event.venue_address_2 &&
                                        <>
                                            - { this.state.event.end_date }
                                        </>
                                    }
                                    </span>
                                </div>
                                
                                <div className="occassiongenius-single-venue-information" data-venue_uuid={ this.state.event.venue_uuid }>
                                    <h4 className="occassiongenius-single-venue-information-title">{ this.state.event.venue_name }</h4>
                                    <address>
                                        { this.state.event.venue_address_1 }<br />
                                        {this.state.event.venue_address_2 &&
                                            <>
                                                { this.state.event.venue_address_2 }<br />
                                            </>
                                        }
                                        { this.state.event.venue_city }, { this.state.event.venue_state } { this.state.event.venue_zip } { this.state.event.venue_country }<br />
                                    </address>
                                </div>

                                {this.state.event.ticket_url &&
                                    <a href={ this.state.event.ticket_url } className="occassiongenius-single-venue-get-tickets" rel="noopener noreferrer" target="_blank" title="Get Tickets">
                                        Get Tickets
                                    </a>
                                }

                                <div className="occassiongenius-single-mapping" data-lat={ this.state.event.latitude } data-long={ this.state.event.longitude }></div>
                                
                            </div>
                        )
                    }
                })()}
            </>
        );

    }

}

export default EventSingle;