import React, { Component } from 'react';
import Loading from "../Components/Loading"
import Breadcrumbs from "../Components/Breadcrumbs"
import EventFilter from '../Components/EventFilter';
import EventGridItem from '../Components/EventGridItem';
class Events extends Component {

    constructor (props){

        super(props);
        this.state = {
            current_page: [],
            next_page: [],
            max_pages: [],
            events: [],
            events_url: '/wp-json/occasiongenius/v1/events?limit=12&page=',
            isLoading: 1,
        }
        
    }
    
    componentDidMount() {

        Promise.all([
          fetch('/wp-json/occasiongenius/v1/events?limit=12'),
        ])
        .then(([res]) => Promise.all([res.json()]))
        .then(([cat_data]) => this.setState({
          events: cat_data.events,
          current_page: cat_data.info.current_page, 
          next_page: cat_data.info.next_page, 
          max_pages: cat_data.info.max_pages, 
          isLoading: 0
        }));

        document.title = "All Local Events";

    } 

    fetchData = async (url) => {
    
        this.setState({
            isLoading: 1
        });
        
        await fetch(url)
          .then((r) => r.json())
          .then((result) => {
            this.setState({
                events: result.events,
                current_page: result.info.current_page, 
                next_page: result.info.next_page, 
                max_pages: result.info.max_pages, 
                isLoading: 0
            });
          })
          .catch((e) => {
            console.log(e);
          });
    
    };  
    
    nextPage = () => {
    
        this.setState({
            current_page: this.state.current_page + 1,
            isLoading: 1
          },
          () => {
            const events_url = this.state.events_url + this.state.current_page;
            this.fetchData(events_url);
            window.scrollTo({
              top: 0,
              behavior: "smooth"
            });
          }
        );
    
    };
    
    prevPage = () => {
    
        this.setState({
            current_page: this.state.current_page - 1,
            isLoading: 1
          },
          () => {
            const events_url = this.state.events_url + this.state.current_page;
            this.fetchData(events_url);
            window.scrollTo({
              top: 0,
              behavior: "smooth"
            });
          }
        );
    
    };    

    render(){

        const { current_page, next_page, max_pages } = this.state;

        return (
            <>

                <Breadcrumbs parent_title="All Categories" parent_url="/events/categories/" page_name="All Local Events" disable_all_events="true" />
                
                <div className="col-span-12">
                    <div className="flow-root">
                        <p className="float-left text-gray-800 text-3xl font-semibold mb-4">
                            All Local Events
                        </p>
                    </div>
                </div>                
                    
      
                <div className="flex w-full flex-wrap">                          

                    <div className="flex flex-col md:w-1/5">
                        <EventFilter fetchData={this.fetchData} />
                    </div>

                    <div className="flex flex-col md:w-4/5 items-center">
                        <div className="grid grid-cols-12 px-18 gap-5">
                            {this.state.isLoading ? (
                                <Loading />
                            ) : (
                                <>                            
                                    {this.state.events?.map((item, index) => (   
                                        <EventGridItem item={item} key={index} />
                                    ))}
                                </>
                            )}                                            
                        </div>
                    </div>
                </div>

                <div className="flex items-center flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6 lg:space-x-8 mt-8 md:mt-16 mt-16">
                    {current_page > 1 &&
                        <>
                            <button onClick={this.prevPage} className="block w-full md:w-3/5 border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:text-white no-underline text-center">
                                Previous Page
                            </button>
                        </>
                    }

                    { next_page <= max_pages && max_pages !== current_page &&
                        <>
                            <button onClick={this.nextPage} className="block w-full md:w-3/5 border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:text-white no-underline text-center">
                                Next Page
                            </button>                    
                        </>
                    }
                </div>

            </>
        )
    }
}

export default Events