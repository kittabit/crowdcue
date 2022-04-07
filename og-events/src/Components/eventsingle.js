import React from 'react';
import RelatedEvents from './RelatedEvents';
import Breadcrumbs from './Breadcrumbs';
import Loading from './Loading';
class EventSingle extends React.Component {

    constructor (props){

        super(props);
        this.state = {
          event: [],
          event_dates: [],
          event_count: [],
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
            event_dates: data.event.event_dates,
            event_count: data.event.event_dates_count,
            isLoading: 0
        }));

    }

    render() {

        return ( 
            <>
                {(() => {
                    if (this.state.isLoading) {
                        document.title = "Loading...";
                        
                        return (
                            <Loading />
                        )
                    } else {
                        if(this.state.event.venue_name){
                            document.title = this.state.event.name + " - " + this.state.event.venue_name + " (" + this.state.event.venue_city + ", " + this.state.event.venue_state + ")";
                        }else{
                            document.title = this.state.event.name;
                        }

                        return (

                            <>
                                <Breadcrumbs page_name={ this.state.event.name } />

                                <div className="container mx-auto" data-popularity={ this.state.event.popularity_score }> 
                                    <div className="mx-auto">
                                        <div className="mt-3 md:mt-4 lg:mt-0 flex flex-col lg:flex-row items-strech justify-center lg:space-x-8">
                                            <div className="lg:w-1/2 flex justify-between items-strech bg-gray-50 bg-cover" style={{ backgroundImage: `url(${this.state.event.image_url})` }}>
                                                <img src={this.state.event.image_url} alt={this.state.event.name} className="w-full h-full invisible" />
                                            </div>
                        
                                            <div className="lg:w-1/2 flex flex-col justify-center mt-7 md:mt-8 lg:mt-0 pb-8 lg:pb-0">
                                                <h1 className="text-3xl lg:text-4xl font-semibold text-gray-800">
                                                    {this.state.event.name}
                                                </h1>
                                                <p className="text-base leading-normal text-gray-600 mt-2">
                                                    { this.state.event.description }
                                                </p>
                                                <p className="text-base leading-normal text-gray-600 mt-2 font-semibold">
                                                    { this.state.event.start_date } 
                                                    {this.state.event.venue_address_2 &&
                                                        <>
                                                            - { this.state.event.end_date }
                                                        </>
                                                    }
                                                </p>
                                                
                                                <p className="text-3xl font-medium text-gray-600 mt-8 md:mt-10"></p>
                            
                                                <div className="flex items-center flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6 lg:space-x-8 mt-8 md:mt-16">
                                                    {this.state.event.ticket_url &&
                                                        <a href={this.state.event.ticket_url} target="_blank" rel="noreferrer" className="block w-full md:w-3/5 border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:text-white no-underline text-center">
                                                            Get Tickets
                                                        </a>
                                                    }

                                                    {this.state.event.source_url &&
                                                        <a href={this.state.event.source_url} target="_blank" rel="noreferrer" className="block w-full md:w-2/5 border border-gray-800 text-base font-medium leading-none text-gray-800 uppercase py-6 bg-transparent focus:outline-none focus:ring-2 focus:ring-offset-2  focus:ring-gray-800 hover:bg-gray-800 hover:text-white no-underline text-center">
                                                            More Information
                                                        </a>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="container mx-auto flex justify-center items-center pt-4 pb-4 border border-solid border-zinc-300 mt-12 bg-slate-100">
                                    <div className="flex flex-col w-3/4 justify-center items-center">
                                        
                                        <h3 className="mt-1 text-2xl font-semibold text-center text-gray-800 text-center md:w-9/12 lg:w-7/12 mb-1 pb-1">
                                            Venue Information
                                        </h3>
                                        <h4 className="mt-1 text-xl font-semibold text-center text-gray-800 text-center md:w-9/12 lg:w-7/12 mb-1 pb-1">
                                            { this.state.event.venue_name }
                                        </h4>
                                        <p className="text-base leading-normal text-center text-gray-600 md:w-9/12 lg:w-7/12 mb-4">
                                            { this.state.event.venue_address_1 }<br />
                                            {this.state.event.venue_address_2 &&
                                                <>
                                                    { this.state.event.venue_address_2 }<br />
                                                </>
                                            }
                                            { this.state.event.venue_city }, { this.state.event.venue_state } { this.state.event.venue_zip } { this.state.event.venue_country }
                                        </p>                                        
                                    </div>
                                </div>

                                <RelatedEvents />

                            </>
                        )
                    }
                })()}
            </>
        );

    }

}

export default EventSingle;