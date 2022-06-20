import React, { Component } from 'react';
import { Link } from "react-router-dom";
import Breadcrumbs from '../Components/Breadcrumbs';
import Loading from '../Components/Loading';
import ReactGA from 'react-ga';
class Categories extends Component {

    constructor (props){

        super(props);
        this.state = {
            categories: [],
            isLoading: 1,
        }
        
    }
    
    componentDidMount() {

        Promise.all([
          fetch('/wp-json/occasiongenius/v1/flags'),
        ])
        .then(([res]) => Promise.all([res.json()]))
        .then(([data]) => this.setState({
          categories: data,
          isLoading: 0
        }));

        document.title = "Local Event Categories";

        if(window.ogSettings.og_ga_ua){
            ReactGA.pageview(window.location.pathname + window.location.search);            
        }          
    } 
    
    render(){

        return (
            <>

                <Breadcrumbs page_name="All Categories" />
                
                {this.state.isLoading ? (
                    <Loading />
                ) : (
                    <div className="flex w-full">
                        <ul className="w-full">
                            {this.state.categories.map((item, index) => (
                            <>
                                <li className="block px-6 py-2 border-b border-gray-200 w-full hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-0 focus:bg-gray-200 focus:text-gray-600 transition duration-500 cursor-pointer" key={index}>
                                    <Link to={`/category/${ item.slug }`} className="no-underline text-gray-800">
                                        { item.output }

                                        <span className="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-red-100 bg-gray-800 rounded-full inline-block float-right">
                                            { item.total }
                                        </span>

                                    </Link>
                                </li>
                            </>
                            ))}
                        </ul>
                    </div>
                )}

            </>
        )
    }
}

export default Categories
