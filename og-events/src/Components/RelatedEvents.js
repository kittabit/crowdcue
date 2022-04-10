import React from 'react';
import Loading from './Loading';
import EventGridItem from "./EventGridItem"
class RelatedEvents extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            events: [],
            search_info: [],
            isLoading: 1
        }
        
    }

    componentDidMount() {

        var related_flags = this.props.flags.join(",");

        Promise.all([
            fetch('/wp-json/occasiongenius/v1/suggested/' + this.props.parent_id + '?flags=' + related_flags + "&limit=8" ),
          ])
          .then(([res]) => Promise.all([res.json()]))
          .then(([suggested_data]) => this.setState({
            events: suggested_data.events,
            search_info: suggested_data.data,
            isLoading: 0
        }));
  
    }

    componentDidUpdate(previousProps, previousState) {

        if (previousProps.parent_id !== this.props.parent_id) {

            var related_flags = this.props.flags.join(",");

            Promise.all([
                fetch('/wp-json/occasiongenius/v1/suggested/' + this.props.parent_id + '?flags=' + related_flags + "&limit=8" ),
              ])
              .then(([res]) => Promise.all([res.json()]))
              .then(([suggested_data]) => this.setState({
                events: suggested_data.events,
                search_info: suggested_data.data,
                isLoading: 0
            }));

        }
        
    }

    render() {;   
        return ( 
            <>
                
                <div className="flex items-center justify-center bg-white mb-16 mt-16">                          
                    <div className="grid grid-cols-12 px-18 gap-5">

                        {this.state.isLoading ? (
                            <Loading />
                        ) : (
                            <>

                                <div className="col-span-12">
                                    <div className="flow-root">
                                        <p className="text-gray-800 text-3xl font-semibold mb-0 text-center">
                                            Other Events You Might Enjoy
                                        </p>
                                    </div>
                                </div>
                                
                                {this.state.events.map((item, index) => (   
                                    <EventGridItem item={item} key={index} />
                                ))}

                            </>
                        )}     
                    </div>
                </div>

            </>

        );

    }

}

export default RelatedEvents;