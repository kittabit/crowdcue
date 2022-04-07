import React from 'react';
import { Link } from "react-router-dom";
import Loading from './Loading';

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
            fetch('/wp-json/occasiongenius/v1/flag/' + this.props.event_cat_id ),
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

                                        <button className="float-right bg-sky-500 text-white px-2 py-2 rounded-md text-1xl font-light hover:bg-sky-700 transition duration-300 mt-[5px] pl-[10px] pr-[10px] uppercase text-base">View All</button>    
                                    </div>
                                </div>

                                {this.state.events.map((item, index) => (   
                                    <div className="col-span-3 bg-rose-700 rounded-xl h-52 md:h-80 no-underline" key={index}>
                                        <Link to={`/events/details/${ item.slug }`} className="no-underline">
                                            <img src={ item.image_url } alt={ item.name } className="rounded-t-xl max-h-44" />
                                            <p className="text-xl text-gray-50 pt-4 pl-3 no-underline text-ellipsis ... overflow-hidden line-clamp-2 h-20 pb-1 mb-0"> { item.name } </p>
                                            <p className="text-xs md:text-lg font-light text-gray-50 pt-0 pl-3 pb-0 mb-0 no-underline"> 
                                                { item.date_formatted } <br />
                                                { item.venue_city }, { item.venue_state }
                                            </p>
                                            <span className="text-xs md:text-lg font-light decoration-white	underline text-white text-center block mt-1 underline-offset-4	">More Info</span>
                                        </Link>
                                    </div>
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