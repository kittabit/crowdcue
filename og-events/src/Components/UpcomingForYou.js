import React from 'react';
import Loading from './Loading';
import { Link } from 'react-router-dom';

class UpcomingForYou extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            events: [],
            isLoading: 1
        }
        
    }

    componentDidMount() {

        Promise.all([
            fetch('/wp-json/occasiongenius/v1/events?limit=4' ),
          ])
          .then(([res]) => Promise.all([res.json()]))
          .then(([cat_data]) => this.setState({
            events: cat_data.events,
            isLoading: 0
        }));
  
    }

    render() {

        return (
            <>
            
                <div className="flex bg-slate-300 og-upcoming-for-you pt-4">
                    <div className="col-span-12">
                        <div className="flow-root">
                            <p className="text-gray-800 font-semibold mb-2 text-2xl pl-4 pr-4">
                                <Link to="/all" className="text-inherit">
                                    <span className="inline-block text-inherit">Upcoming Events</span>
                                    <svg className="w-6 inline-block pl-2 text-inherit" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504.3 273.6l-112.1 104c-6.992 6.484-17.18 8.218-25.94 4.406c-8.758-3.812-14.42-12.45-14.42-21.1L351.9 288H32C14.33 288 .0002 273.7 .0002 255.1S14.33 224 32 224h319.9l0-72c0-9.547 5.66-18.19 14.42-22c8.754-3.809 18.95-2.075 25.94 4.41l112.1 104C514.6 247.9 514.6 264.1 504.3 273.6z"/></svg>
                                </Link>
                            </p>
                        </div>
                    </div>  
                </div>                  
                <div className="flex bg-slate-300 og-upcoming-for-you pt-2 pb-4 pl-4 pr-4">
                    {this.state.isLoading ? (

                        <Loading />

                    ) : (
                        <>
                            <div class="flex">
                                <div className="grid grid-cols-4 gap-4">

                                    {this.state.events.map((item, index) => (   
                                        <>
                                            <div className="bg-white p-3 relative min-h-[120px]" key={index}>
                                                <div className="grid grid-cols-4 gap-1">
                                                    <div className="bg-gray-800 col-span-1 text-center rounded-lg">
                                                        <span className="block text-white leading-none pt-2">{item.start_date_day}</span>
                                                        <span className="block text-white leading-none pb-2">{item.start_date_month}</span>
                                                    </div>
                                                    <div className="col-span-3">
                                                        <Link to={`/details/${ item.slug }/`} className="pl-2 no-underline text-gray-600 text-ellipsis ... overflow-hidden line-clamp-2 text-base">
                                                            {item.name}
                                                        </Link>
                                                    </div>
                                                </div>
                                                <div className="absolute bottom-2 left-3">
                                                    <svg className="w-2 inline-block fill-inherit" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg>
                                                    <span className="pl-2 text-xs">{item.venue_city}, {item.venue_state}</span>
                                                </div>
                                            </div>
                                        </>
                                    ))}

                                </div>
                            </div>
                        </>
                    )}  

                </div>
            </>
        );

    }

}

export default UpcomingForYou;