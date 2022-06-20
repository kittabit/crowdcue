import React, { Component } from 'react';
import Breadcrumbs from './Breadcrumbs';
import Loading from './Loading';
import EventGridItem from "./EventGridItem"
import { Link } from 'react-router-dom';
import ReactGA from 'react-ga';
class CategoryOutput extends Component {

    constructor (props){

        super(props);
        this.state = {
            current_page: [],
            next_page: [],
            max_pages: [],
            category_data: [],
            category_events: [],
            total_events: 0,
            events_url: '/wp-json/occasiongenius/v1/flag/' + this.props.slug,
            isLoading: 1,
        }
        
    }
    
    componentDidMount() {

        Promise.all([
          fetch('/wp-json/occasiongenius/v1/flag/' + this.props.slug),
        ])
        .then(([res]) => Promise.all([res.json()]))
        .then(([cat_data]) => this.setState({
          category_data: cat_data.data,
          category_events: cat_data.events,
          current_page: cat_data.info.current_page, 
          next_page: cat_data.info.next_page, 
          max_pages: cat_data.info.max_pages, 
          total_events: cat_data.info.total,
          isLoading: 0
        }));

        document.title = "All Categories for Local Events";

        if(window.ogSettings.og_ga_ua){
            ReactGA.pageview(window.location.pathname + window.location.search);            
        }          
    } 

    fetchData = async (url) => {
    
        await fetch(url)
          .then((r) => r.json())
          .then((result) => {
            this.setState({
                category_events: result.events,
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
            const events_url = this.state.events_url + "?page=" + this.state.current_page;
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
            const events_url = this.state.events_url + "?page=" + this.state.current_page;
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

        document.title = this.state.category_data.Output + " | Local Events";

        return (
            <>

                <Breadcrumbs parent_title="All Categories" parent_url="/categories/" page_name={ this.state.category_data.Output } />
                
                <div className="flex items-center justify-center bg-white mb-16">                          
                    <div className="grid grid-cols-12 px-18 gap-5">

                        {this.state.isLoading ? (

                            <Loading />

                        ) : (
                            <>

                                <div className="col-span-12">
                                    <div className="flow-root">
                                        <p className="float-left text-gray-800 text-3xl font-semibold mb-0">
                                            { this.state.category_data.Output }
                                        </p>
                                    </div>
                                </div>

                                {this.state.total_events > 0 &&
                                    <>
                                        {this.state.category_events.map((item, index) => (   
                                            <EventGridItem item={item} key={index} />
                                        ))}
                                    </>
                                }

                                {this.state.total_events === 0 &&
                                    <>
                                        <div className="col-span-12 text-center mb-12 w-full min-w-full">
                                            <p className="text-gray-700 ml-1 md:ml-2 text-sm font-medium">There were no events found for this category, please try a <Link to="/events/categories/" className="text-gray-700 hover:text-gray-900 text-sm font-medium">different category</Link>.</p>
                                        </div>
                                    </>
                                }

                            </>
                        )}        
                    </div> 
                </div>

                <div className="flex items-center flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6 lg:space-x-8 mt-8 md:mt-16">
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

export default CategoryOutput
