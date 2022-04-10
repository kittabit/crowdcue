import React from 'react';
import { Link } from "react-router-dom";
import Loading from './Loading';
import EventGridItem from "./EventGridItem"

class PersonalizedEvents extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            isLoading: 1
        }
        
    }

    componentDidMount() {

        this.setState({
            isLoading: 0
        });
  
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
                                            Recommended Events For You
                                        </p>


                                    </div>
                                </div>

                                {this.props.events.map((item, index) => (   
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

export default PersonalizedEvents;