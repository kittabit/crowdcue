import React, { Component } from 'react';
import Breadcrumbs from '../Components/Breadcrumbs';
import Loading from '../Components/Loading';
import EventGridItem from '../Components/EventGridItem';
import ReactGA from 'react-ga';

class ForYou extends Component {

    constructor (props){

        super(props);
        this.state = {
            categories: [],
            isLoading: 1,
        }
        
    }
    
    componentDidMount() {

        if(localStorage.getItem('og_user_liked') !== null){

            var og_user_liked = JSON.parse(localStorage.getItem('og_user_liked'));

            Promise.all([
                fetch('/wp-json/occasiongenius/v1/bucket?limit=disabled&events=' + og_user_liked.join(",")),
              ])
              .then(([res]) => Promise.all([res.json()]))
              .then(([data]) => this.setState({
                user_liked_events: data.events,
                user_liked_events_count: data.total,
                isLoading: 0
            }));

        } 

        document.title = "Local Event Categories";

        if(window.ogSettings.og_ga_ua){
            ReactGA.pageview(window.location.pathname + window.location.search);            
        }          
    } 
    
    render(){

        return (
            <>

                <Breadcrumbs page_name="For You" />
                
                <div className="flex items-center justify-center bg-white mb-16">                          
                    <div className="grid grid-cols-12 px-18 gap-5">
                        <div className="col-span-12">
                            <div className="flow-root">
                                <p className="float-left text-gray-800 text-3xl font-semibold mb-0">
                                    For You
                                </p>
                            </div>
                        </div>

                        {this.state.isLoading ? (
                            <Loading />
                        ) : (
                            <>
                                {this.state.user_liked_events_count > 0 &&
                                    <>
                                        {this.state.user_liked_events.map((item, index) => (   
                                            <EventGridItem item={item} key={index} />
                                        ))}
                                    </>
                                }
                            </>
                        )}
                    </div>
                </div>

            </>
        )
    }
}

export default ForYou
