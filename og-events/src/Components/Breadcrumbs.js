import React from 'react';
import { Link } from "react-router-dom";

class Breadcrumbs extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            event_details_flags: []
        }
        
    }

    componentDidMount() {

        if(this.props.page_flags){
            const event_flags_url = "/wp-json/occasiongenius/v1/event_flags?flags=" + this.props.page_flags;

            Promise.all([
                fetch(event_flags_url)
            ])      
            .then(([res]) => Promise.all([res.json()]))
            .then(([data]) => this.setState({            
                event_details_flags: data,
                isLoading: 0
            }));
        }

    }
    
    render() {

        return ( 
            <>
                
                <nav className="flex mb-3" aria-label="Breadcrumb">
                    <ol className="inline-flex items-center space-x-1 ml-0 pl-0">

                        <li>
                            <div className="flex items-center">
                                <Link to="/" className="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">
                                    Events
                                </Link>
                            </div>
                        </li>

                        {this.state.event_details_flags &&
                            <>
                            {this.props.disable_all_events !== "true" &&
                                <>
                                <li>
                                    <div className="flex items-center">
                                        <svg className="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                        <Link to="/all" className="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">
                                            All Events
                                        </Link>
                                    </div>
                                </li>
                                </>
                            }

                                {this.state.event_details_flags.map((item, index) => (   
                                    <li>
                                        <div className="flex items-center" key={index}>
                                            <svg className="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                            <Link to={`/category/${ item.slug }/`} className="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">
                                                { item.name }
                                            </Link>
                                        </div>
                                    </li>                                    
                                ))}
                            </>
                        }

                        {this.props.parent_url &&
                            <>
                            <li>
                                <div className="flex items-center">
                                    <svg className="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    <Link to={this.props.parent_url} className="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">
                                        { this.props.parent_title }
                                    </Link>
                                </div>
                            </li>
                            </>
                        }

                        <li aria-current="page">
                            <div className="flex items-center">
                                <svg className="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span className="text-gray-400 ml-1 md:ml-2 text-sm font-medium">
                                    { this.props.page_name }
                                </span>
                            </div>
                        </li>
                    </ol>
                </nav>

            </>

        );

    }

}

export default Breadcrumbs;