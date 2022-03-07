import React from 'react';
import { Link } from "react-router-dom";
class Event extends React.Component {

    // event schema
    // location, Name of venue, detailed address, name of event, startDate, description, endDate, img
    
    render() {

        return ( 

            <div className="occasiongenius-single-item occasiongenius-tile" itemscope itemtype="https://schema.org/Event">
                <Link to={`/events/details/${this.props.data.slug}`}>
                    <div className="occasiongenius-single_image" style={{ backgroundImage: `url(${this.props.data.image_url})` }}>
                        <img src={ this.props.data.image_url } alt={ this.props.data.name } title={ this.props.data.title } loading="lazy" />
                    </div>
                    <div className="occasiongenius-event-location">
                        <div className="occasiongenius-event-location-left">
                            <span className="occasiongenius-single_title" itemprop="name">{ this.props.data.name }</span>
                            <span className="occasiongenius-single_location">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg>
                                { this.props.data.venue_name }                            
                            </span> 
                        </div>
                        <div className="occasiongenius-event-location-right">
                            <span className="occasiongenius-single_date_day">{ this.props.data.start_date_day }</span>
                            <span className="occasiongenius-single_date_month">{ this.props.data.start_date_month }</span>
                        </div>

                        <div className="occasiongenius_clear"></div>
                    </div>
                </Link>
            </div>

        );

    }

}

export default Event;