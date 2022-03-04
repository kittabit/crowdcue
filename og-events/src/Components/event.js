import React from 'react';
import { Link } from "react-router-dom";
class Event extends React.Component {

    render() {

        return ( 

            <div className="occasiongenius-single-item">
                <Link to={`/events/details/${this.props.data.uuid}`}>
                    <div className="occasiongenius-single_image" style={{ backgroundImage: `url(${this.props.data.image_url})` }}>
                        <img src={ this.props.data.image_url } alt={ this.props.data.name } title={ this.props.data.title } loading="lazy" />
                    </div>
                    <span className="occasiongenius-single_title">{ this.props.data.name }</span>
                    <span className="occasiongenius-single_location">{ this.props.data.venue_city }, { this.props.data.venue_state }</span>
                    <span className="occasiongenius-single_date">{ this.props.data.start_date }</span>
                </Link>
            </div>

        );

    }

}

export default Event;