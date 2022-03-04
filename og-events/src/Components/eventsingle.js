import React from 'react';
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

            <div className="occasiongenius-single-page">
                
                <h4>[work_in_progress]</h4>

                <span>Name: </span> { this.state.event.name } (#{this.props.uuid}<br />
                <span>popularity_score: </span> { this.state.event.popularity_score }<br />
                <span>description: </span> { this.state.event.description }<br />
                <span>start_date: </span> { this.state.event.start_date }<br />
                <span>end_date: </span> { this.state.event.end_date }<br />
                <span>source_url: </span> { this.state.event.source_url }<br />
                <span>ticket_url: </span> { this.state.event.ticket_url }<br />
                <span>venue_name: </span> { this.state.event.venue_name }<br />
                <span>venue_uuid: </span> { this.state.event.venue_uuid }<br />
                <span>venue_address_1: </span> { this.state.event.venue_address_1 }<br />
                <span>venue_address_2: </span> { this.state.event.venue_address_2 }<br />
                <span>venue_city: </span> { this.state.event.venue_city }<br />
                <span>venue_state: </span> { this.state.event.venue_state }<br />
                <span>venue_zip: </span> { this.state.event.venue_zip }<br />
                <span>venue_country: </span> { this.state.event.venue_country }<br />
                <span>latitude: </span> { this.state.event.latitude }<br />
                <span>longitude: </span> { this.state.event.longitude }<br />
                
            </div>

        );

    }

}

export default EventSingle;