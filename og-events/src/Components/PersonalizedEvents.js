import React from 'react';
import Loading from './Loading';
import EventGridItem from "./EventGridItem"
import { Link } from 'react-router-dom';
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

        let personalizedClassName = 'flex items-center justify-center bg-white mb-16';
        let clearDivClassName = '';
        let biggerGridItems = '0';
        let blockTitleClassName = "float-left text-gray-800 font-semibold mb-0";


        if (this.props.likes === 4 && this.props.personalized === 4 && this.props.clear === "true") {
            clearDivClassName = "clear-both";
            personalizedClassName += ' w-1/2 float-left pl-2';
            biggerGridItems = '1';
            blockTitleClassName += " text-2xl";
        }else if (this.props.likes === 4 && this.props.personalized === 4) {
            personalizedClassName += ' w-1/2 float-left pr-2';
            biggerGridItems = '1';
            blockTitleClassName += " text-2xl";
        }else{
            blockTitleClassName += " text-3xl";
        }

        return ( 
            <>

                <div className={ personalizedClassName }>                          
                    <div className="grid grid-cols-12 px-18 gap-5">

                        {this.state.isLoading ? (

                            <Loading />

                        ) : (
                            <>

                                <div className="col-span-12">
                                    <div className="flow-root">
                                        <p className={ blockTitleClassName }>
                                            {this.props.title ? (
                                                <>
                                                    { this.props.title }
                                                </>
                                            ) : (
                                                <>
                                                    Recommended Events For You
                                                </>
                                            )}                                            
                                        </p>

                                        {this.props.liked &&
                                            <>
                                                <Link to="/for-you" className="no-underline">
                                                    <button className="float-right bg-gray-800 text-white px-2 py-2 rounded-none text-base font-medium hover:bg-gray-800 transition duration-300 mt-[5px] pl-[15px] pr-[15px] uppercase text-base pt-[12px] leading-none hover:bg-gray-600 mt-0">
                                                        See More
                                                    </button>    
                                                </Link>
                                            </>
                                        }

                                    </div>
                                </div>

                                {this.props.events.map((item, index) => (   
                                    <EventGridItem item={item} key={index} largerGrid={ biggerGridItems } />
                                ))}

                            </>
                        )}     

                    </div> 
                </div>

                <div className={ clearDivClassName }></div>

            </>

        );

    }

}

export default PersonalizedEvents;