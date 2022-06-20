import React from 'react';
import { Link } from "react-router-dom";
import Loading from './Loading';
import EventGridItem from "./EventGridItem"
class EventCategorySmall extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            events: [],
            category: [],
            isLoading: 1
        }
        
    }

    componentDidMount() {

        Promise.all([
            fetch('/wp-json/occasiongenius/v1/flag/' + this.props.event_cat_id + '?limit=4' ),
          ])
          .then(([res]) => Promise.all([res.json()]))
          .then(([cat_data]) => this.setState({
            events: cat_data.events,
            category: cat_data.data,
            isLoading: 0
        }));
  
    }

    render() {

        return ( 
            <>

                <div className="flex items-center justify-center bg-white mb-16">                          
                    <div className="grid grid-cols-12 px-18 gap-5">

                        {this.state.isLoading ? (

                            <Loading />

                        ) : (
                            <>

                                <div className="col-span-12">
                                    <div className="flow-root">
                                        <p className="float-left text-gray-800 text-3xl font-semibold mb-0">
                                            { this.state.category.Output }
                                        </p>

                                        <Link to={`/category/${ this.state.category.Name }`} className="no-underline">
                                            <button className="float-right bg-gray-800 text-white px-2 py-2 rounded-none text-base font-medium hover:bg-gray-800 transition duration-300 mt-[5px] pl-[15px] pr-[15px] uppercase text-base pt-[12px] leading-none hover:bg-gray-600">View All</button>    
                                        </Link>
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

export default EventCategorySmall;