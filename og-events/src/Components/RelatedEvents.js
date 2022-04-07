import React from 'react';
import { Link } from "react-router-dom";

class RelatedEvents extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            events: [],
            isLoading: 1
        }
        
    }

    componentDidMount() {

        // Promise.all([
        //     fetch('/wp-json/occasiongenius/v1/flag/' + this.props.event_cat_id ),
        //   ])
        //   .then(([res]) => Promise.all([res.json()]))
        //   .then(([cat_data]) => this.setState({
        //     events: cat_data.events,
        //     category: cat_data.data,
        //     isLoading: 0
        // }));
  
    }

    render() {

        return ( 
            <>
                
                <div class="flex justify-center items-center">
                    <div class="2xl:mx-auto 2xl:container w-96 sm:w-auto pt-2 mt-8">
                        <div role="main" class="flex flex-col items-center justify-center">
                            <h4 class="text-2xl font-semibold text-center text-gray-800 mt-0 mb-0 pt-0 pb-0">Other Events Your Might Enjoy</h4>
                        </div>
                        
                        <div class="lg:flex items-stretch md:mt-6 mt-6">
                            <div class="lg:w-1/2">
                                <div class="sm:flex items-center justify-between xl:gap-x-8 gap-x-6">

                                    <div class="sm:w-1/2 relative">
                                        <div>
                                            <p class="p-6 text-xs font-medium leading-3 text-white absolute top-0 right-0">12 April 2021</p>
                                            <div class="absolute bottom-0 left-0 p-6">
                                                <h2 class="text-xl font-semibold 5 text-white">The Decorated Ways</h2>
                                                <p class="text-base leading-4 text-white mt-2">Dive into minimalism</p>
                                                <a href="javascript:void(0)" class="focus:outline-none focus:underline flex items-center mt-4 cursor-pointer text-white hover:text-gray-200 hover:underline">
                                                    <p class="pr-2 text-sm font-medium leading-none">Read More</p>                        
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <img src="https://i.ibb.co/DYxtCJq/img-1.png" class="w-full" alt="chair" />
                                    </div>
                                    
                                    <div class="sm:w-1/2 sm:mt-0 mt-4 relative">
                                        <div>
                                            <p class="p-6 text-xs font-medium leading-3 text-white absolute top-0 right-0">12 April 2021</p>
                                            <div class="absolute bottom-0 left-0 p-6">
                                                <h2 class="text-xl font-semibold 5 text-white">The Decorated Ways</h2>
                                                <p class="text-base leading-4 text-white mt-2">Dive into minimalism</p>
                                                <a href="javascript:void(0)" class="focus:outline-none focus:underline flex items-center mt-4 cursor-pointer text-white hover:text-gray-200 hover:underline">
                                                    <p class="pr-2 text-sm font-medium leading-none">Read More</p>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <img src="https://i.ibb.co/3C5HvxC/img-2.png" class="w-full" alt="wall design" />
                                    </div>
                                </div>
                                
                                <div class="relative">
                                    <div>
                                        <p class="md:p-10 p-6 text-xs font-medium leading-3 text-white absolute top-0 right-0">12 April 2021</p>
                                        <div class="absolute bottom-0 left-0 md:p-10 p-6">
                                            <h2 class="text-xl font-semibold 5 text-white">The Decorated Ways</h2>
                                            <p class="text-base leading-4 text-white mt-2">Dive into minimalism</p>
                                            <a href="javascript:void(0)" class="focus:outline-none focus:underline flex items-center mt-4 cursor-pointer text-white hover:text-gray-200 hover:underline">
                                                <p class="pr-2 text-sm font-medium leading-none">Read More</p>                
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <img src="https://i.ibb.co/Ms4qyXp/img-3.png" alt="sitting place" class="w-full mt-8 md:mt-6 hidden sm:block" />
                                    <img class="w-full mt-4 sm:hidden" src="https://i.ibb.co/6XYbN7f/Rectangle-29.png" alt="sitting place" />
                                </div>

                            </div>
                            
                            <div class="lg:w-1/2 xl:ml-8 lg:ml-4 lg:mt-0 md:mt-6 mt-4 lg:flex flex-col justify-between">

                                <div class="relative">
                                    <div>
                                        <p class="md:p-10 p-6 text-xs font-medium leading-3 text-white absolute top-0 right-0">12 April 2021</p>
                                        <div class="absolute bottom-0 left-0 md:p-10 p-6">
                                            <h2 class="text-xl font-semibold 5 text-white">The Decorated Ways</h2>
                                            <p class="text-base leading-4 text-white mt-2">Dive into minimalism</p>
                                            <a href="javascript:void(0)" class="focus:outline-none focus:underline flex items-center mt-4 cursor-pointer text-white hover:text-gray-200 hover:underline">
                                                <p class="pr-2 text-sm font-medium leading-none">Read More</p>
                                            </a>
                                        </div>
                                    </div>

                                    <img src="https://i.ibb.co/6Wfjf2w/img-4.png" alt="sitting place" class="w-full sm:block hidden" />
                                    <img class="w-full sm:hidden" src="https://i.ibb.co/dpXStJk/Rectangle-29.png" alt="sitting place" />
                                </div>

                                <div class="sm:flex items-center justify-between xl:gap-x-8 gap-x-6 md:mt-6 mt-4">

                                    <div class="relative w-full">
                                        <div>
                                            <p class="p-6 text-xs font-medium leading-3 text-white absolute top-0 right-0">12 April 2021</p>
                                            <div class="absolute bottom-0 left-0 p-6">
                                                <h2 class="text-xl font-semibold 5 text-white">The Decorated Ways</h2>
                                                <p class="text-base leading-4 text-white mt-2">Dive into minimalism</p>
                                                <a href="javascript:void(0)" class="focus:outline-none focus:underline flex items-center mt-4 cursor-pointer text-white hover:text-gray-200 hover:underline">
                                                    <p class="pr-2 text-sm font-medium leading-none">Read More</p>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <img src="https://i.ibb.co/3yvZBpm/img-5.png" class="w-full" alt="chair" />
                                    </div>
                                    
                                    <div class="relative w-full sm:mt-0 mt-4">
                                        <div>
                                            <p class="p-6 text-xs font-medium leading-3 text-white absolute top-0 right-0">12 April 2021</p>
                                            <div class="absolute bottom-0 left-0 p-6">
                                                <h2 class="text-xl font-semibold 5 text-white">The Decorated Ways</h2>
                                                <p class="text-base leading-4 text-white mt-2">Dive into minimalism</p>
                                                <a href="javascript:void(0)" class="focus:outline-none focus:underline flex items-center mt-4 cursor-pointer text-white hover:text-gray-200 hover:underline">
                                                    <p class="pr-2 text-sm font-medium leading-none">Read More</p>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <img src="https://i.ibb.co/gDdnJb5/img-6.png" class="w-full" alt="wall design" />
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </>

        );

    }

}

export default RelatedEvents;