import React from 'react';
import { Link } from 'react-router-dom';
import RelatedEvents from './RelatedEvents';
import Breadcrumbs from './Breadcrumbs';
import Loading from './Loading';
import OGUserLogging from './Tools/OGUserLogging';
import MapOutput from "./MapOutput"
import ReactGA from 'react-ga';
class EventSingle extends React.Component {

    constructor (props){

        super(props);
        this.state = {
          event: [],
          isLoading: 1,
          events_url: "/wp-json/occasiongenius/v1/event/"
        }
        
        this.dislikeEvent = this.dislikeEvent.bind(this);
        this.likeEvent = this.likeEvent.bind(this);
        this.eventLikedStatus = this.eventLikedStatus.bind(this);
        this.checkUserEventLikeStatus = this.checkUserEventLikeStatus.bind(this);
        
    }

    componentDidMount() {

        const event_url = this.state.events_url + this.props.uuid;

        Promise.all([
            fetch(event_url)
        ])      
        .then(([res]) => Promise.all([res.json()]))
        .then(([data]) => {                
            this.setState({            
                event: data.event,
                isLoading: 0
            
            })

            this.checkUserEventLikeStatus();
        });

        OGUserLogging("", this.props.uuid );

        if(window.ogSettings.og_ga_ua){
            ReactGA.pageview(window.location.pathname + window.location.search);
            ReactGA.event({
                category: 'Events',
                action: 'Viewed Event',
                label: this.state.event.name,
                value: 1
            });
        }            

    }

    componentDidUpdate(previousProps, previousState) {

        if (previousProps.uuid !== this.props.uuid) {

            const event_url = this.state.events_url + this.props.uuid;

            Promise.all([
                fetch(event_url)
            ])      
            .then(([res]) => Promise.all([res.json()]))
            .then(([data]) => {                
                this.setState({            
                    event: data.event,
                    isLoading: 0
                
                })

                this.checkUserEventLikeStatus();
            });

            OGUserLogging("", this.props.uuid );

            window.scrollTo(0, 0)

            if(window.ogSettings.og_ga_ua){
                ReactGA.pageview(window.location.pathname + window.location.search);
                ReactGA.event({
                    category: 'Events',
                    action: 'Viewed Event',
                    label: this.state.event.name,
                    value: 1
                });                
            }          

        }

    }

    dislikeEvent(){

        console.log(":: Action: Dislike Event");

        if(this.state.disliked){

            this.setState({            
                disliked: false
            });

            this.eventLikedStatus("disliked", this.state.event.uuid, "true");

        }else{

            this.setState({            
                disliked: true,
                liked: false
            });

            this.eventLikedStatus("disliked", this.state.event.uuid);

        }

    }

    likeEvent(){

        console.log(":: Action: Like Event");

        if(this.state.liked){

            this.setState({            
                liked: false,
            });

            this.eventLikedStatus("liked", this.state.event.uuid, "true");

        }else{

            this.setState({            
                liked: true,
                disliked: false
            });

            this.eventLikedStatus("liked", this.state.event.uuid);

        }

    }

    eventRemoveFromArray(status, uuid){

        var local_storage_key = "";

        if(status === "liked"){
            local_storage_key = "og_user_liked";
        }else{
            local_storage_key = "og_user_disliked";
        }

        console.log(":: Action: Removing Event from `" + local_storage_key + "` for `" + uuid + "`");

        var user_data = JSON.parse(localStorage.getItem(local_storage_key));
        user_data = user_data.filter(function(e) { return e !== uuid })
        localStorage.setItem(local_storage_key, JSON.stringify(user_data));

    }

    eventLikedStatus(status=null, uuid=null, remove=null){
        
        if(localStorage.getItem('og_user_liked') !== null){
            var og_user_liked = JSON.parse(localStorage.getItem('og_user_liked'));
        }

        if(localStorage.getItem('og_user_disliked') !== null){
            var og_user_disliked = JSON.parse(localStorage.getItem('og_user_disliked'));
        }

        if(status !== null && uuid !== null){

            if(remove === "true"){

                this.eventRemoveFromArray(status, uuid);

            }else{

                if(status === "liked"){

                    if(remove === "true"){

                    }else{
                        if(og_user_liked){
                            og_user_liked.push( uuid );
                        }else{
                            og_user_liked = [ uuid ];
                        }
                        localStorage.setItem('og_user_liked', JSON.stringify(og_user_liked));
                    }
                    
                }else if(status === "disliked"){

                    if(og_user_disliked){
                        og_user_disliked.push( uuid );
                    }else{
                        og_user_disliked = [ uuid ];
                    }
                    localStorage.setItem('og_user_disliked', JSON.stringify(og_user_disliked));

                }

            }

        }

    }

    checkUserEventLikeStatus(){

        if(localStorage.getItem('og_user_liked') !== null){

            var og_user_liked = JSON.parse(localStorage.getItem('og_user_liked'));
            if(Array.isArray(og_user_liked) && og_user_liked.includes(this.state.event.uuid)){
                this.setState({            
                    liked: true
                });
            }else{
                this.setState({            
                    liked: false
                });
            }
    
        }

        if(localStorage.getItem('og_user_disliked') !== null){

            var og_user_disliked = JSON.parse(localStorage.getItem('og_user_disliked'));
            if(Array.isArray(og_user_disliked) && og_user_disliked.includes(this.state.event.uuid)){
                this.setState({            
                    disliked: true
                });
            }else{
                this.setState({            
                    disliked: false
                });                
            } 

        }

    }

    render() {

        let dislikedClassNames = 'w-8 h-8 hover:fill-rose-500 cursor-pointer';
        let likedClassNames = 'w-8 h-8 hover:fill-blue-500 cursor-pointer';
        
        if (this.state.disliked) {
            dislikedClassNames += ' fill-rose-500';
        }else{
            dislikedClassNames += ' fill-gray-500';
        }

        if (this.state.liked) {
            likedClassNames += ' fill-blue-500';
        }else{
            likedClassNames += ' fill-gray-500';
        }     

        return ( 
            <>
                {(() => {
                    if (this.state.isLoading) {

                        document.title = "Loading...";
                        
                        return (

                            <Loading />
                        )

                    } else { 

                        if(this.state.event.error){

                            return (

                                <>

                                    <Breadcrumbs page_name="Event Not Found" />

                                    <div className="col-span-12 text-center mb-12">
                                        <p className="text-gray-800 ml-1 md:ml-2 text-sm font-medium">There were no events found matching your request, please <Link to="/categories">view all categories</Link> or <Link to="/">return to all events</Link>.</p>
                                    </div>

                                </>

                            )

                        }else{

                            if(this.state.event.venue_name){
                                document.title = this.state.event.name + " - " + this.state.event.venue_name + " (" + this.state.event.venue_city + ", " + this.state.event.venue_state + ")";
                            }else{
                                document.title = this.state.event.name;
                            }                     
    
                            return (

                                <>                                

                                    <Breadcrumbs page_flags={ this.state.event.flags} page_name={ this.state.event.name } />

                                    <div className="container mx-auto" data-uuid={ this.state.event.uuid } data-popularity={ this.state.event.popularity_score }> 
                                        <div className="mx-auto">
                                            <div className="mt-3 md:mt-4 lg:mt-0 flex flex-col lg:flex-row items-strech justify-center lg:space-x-8">
                                                <div className="lg:w-1/2 flex justify-between items-strech bg-gray-50 bg-cover bg-center rounded-xl relative" style={{ backgroundImage: `url(${this.state.event.image_url})` }}>
                                                    <img src={this.state.event.image_url} alt={this.state.event.name} className="w-full h-full invisible" itemprop="image" content={ this.state.event.image_url } />

                                                    <div className="absolute z-10 bottom-2.5 left-2.5 has-tooltip rounded-full	bg-white p-2" data-disliked={ this.state.disliked }>
                                                        <svg onClick={ this.dislikeEvent} className={ dislikedClassNames } xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M96 32.04H32c-17.67 0-32 14.32-32 31.1v223.1c0 17.67 14.33 31.1 32 31.1h64c17.67 0 32-14.33 32-31.1V64.03C128 46.36 113.7 32.04 96 32.04zM467.3 240.2C475.1 231.7 480 220.4 480 207.9c0-23.47-16.87-42.92-39.14-47.09C445.3 153.6 448 145.1 448 135.1c0-21.32-14-39.18-33.25-45.43C415.5 87.12 416 83.61 416 79.98C416 53.47 394.5 32 368 32h-58.69c-34.61 0-68.28 11.22-95.97 31.98L179.2 89.57C167.1 98.63 160 112.9 160 127.1l.1074 160c0 0-.0234-.0234 0 0c.0703 13.99 6.123 27.94 17.91 37.36l16.3 13.03C276.2 403.9 239.4 480 302.5 480c30.96 0 49.47-24.52 49.47-48.11c0-15.15-11.76-58.12-34.52-96.02H464c26.52 0 48-21.47 48-47.98C512 262.5 492.2 241.9 467.3 240.2z"/></svg>

                                                        <span className='tooltip block bg-white p-2 -top-2 lg:-top-2 -right-40 text-gray-600 text-base leading-normal z-20'>
                                                            <div class="w-11 overflow-hidden inline-block absolute -left-[1.8rem] z-10">
                                                                <div class="h-8 bg-white -rotate-45 transform origin-top-right"></div>
                                                            </div>

                                                            <span className="relative z-30">Dislike this Event</span>
                                                        </span>
                                                    </div>

                                                    <div className="absolute z-10 bottom-2.5 right-2.5 has-tooltip rounded-full	bg-white p-2" data-disliked={ this.state.liked }>
                                                        <svg onClick={ this.likeEvent } className={ likedClassNames } xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M128 447.1V223.1c0-17.67-14.33-31.1-32-31.1H32c-17.67 0-32 14.33-32 31.1v223.1c0 17.67 14.33 31.1 32 31.1h64C113.7 479.1 128 465.6 128 447.1zM512 224.1c0-26.5-21.48-47.98-48-47.98h-146.5c22.77-37.91 34.52-80.88 34.52-96.02C352 56.52 333.5 32 302.5 32c-63.13 0-26.36 76.15-108.2 141.6L178 186.6C166.2 196.1 160.2 210 160.1 224c-.0234 .0234 0 0 0 0L160 384c0 15.1 7.113 29.33 19.2 38.39l34.14 25.59C241 468.8 274.7 480 309.3 480H368c26.52 0 48-21.47 48-47.98c0-3.635-.4805-7.143-1.246-10.55C434 415.2 448 397.4 448 376c0-9.148-2.697-17.61-7.139-24.88C463.1 347 480 327.5 480 304.1c0-12.5-4.893-23.78-12.72-32.32C492.2 270.1 512 249.5 512 224.1z"/></svg>

                                                        <span className='tooltip block bg-white p-2 -top-2 lg:-top-2 -left-36 text-gray-600 text-base leading-normal z-20'>
                                                            <span className="relative z-30">Like this Event</span>

                                                            <div className="w-11 overflow-hidden inline-block absolute -right-[1.8rem] z-10">
                                                                <div className=" h-8 bg-white rotate-45 transform origin-top-left"></div>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                            
                                                <div className="lg:w-1/2 flex flex-col justify-center mt-7 md:mt-8 lg:mt-0 pb-8 lg:pb-0">
                                                    <h1 className="text-3xl lg:text-4xl font-semibold text-gray-800" itemprop="name">
                                                        {this.state.event.name}
                                                    </h1>
                                                    <p className="text-base leading-normal text-gray-600 mt-2" itemprop="description">
                                                        { this.state.event.description }
                                                    </p>
                                                    <p className="text-base leading-normal text-gray-600 mt-2 font-semibold" itemprop="startDate" datetime={ this.state.event.gcal_start_date }>
                                                        { this.state.event.date_formatted } 
                                                    </p>
                                                                        
                                                    <div className="w-full mx-auto">
                                                        <h4 className="text-lg font-bold m-0 p-0 mb-1 bg-transparent">Share This Event:</h4>
                                                        <div className="flex">
                                                            <ul className="flex flex-wrap m-0 p-0">
                                                                
                                                                <li className="p-0 m-0">
                                                                    <a href={`https://www.facebook.com/sharer/sharer.php?u=${ window.location.href }`} target="_blank" rel="noreferrer" className="inline-flex items-center mr-1 px-3 py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-sm text-gray-600 hover:text-gray-800 no-underline" title="Share this event to Facebook">
                                                                    <span>
                                                                        <svg className="w-auto h-4 mr-2 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                                                        <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"></path>
                                                                        </svg>
                                                                    </span>
                                                                    Facebook
                                                                    </a>
                                                                </li>    

                                                                <li className="p-0 m-0">
                                                                    <a href={`https://twitter.com/intent/tweet?text=${this.state.event.name} - ${ window.location.href }`} target="_blank" rel="noreferrer" className="inline-flex items-center mr-1 px-3 py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-sm text-gray-600 hover:text-gray-800 no-underline" title="Share this event to Twitter">
                                                                    <span>
                                                                        <svg className="w-auto h-4 mr-2 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                                        <path
                                                                                d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"
                                                                                ></path>
                                                                        </svg>
                                                                    </span>
                                                                    Twitter
                                                                    </a>
                                                                </li>       
                                                                                                                    
                                                                <li className="p-0 m-0">
                                                                    <a href={`https://www.linkedin.com/sharing/share-offsite/?url=${ window.location.href }`} target="_blank" rel="noreferrer" className="inline-flex items-center mr-1 px-3 py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-sm text-gray-600 hover:text-gray-800 no-underline" title="Share this event to LinkedIn">
                                                                    <span>
                                                                        <svg className="w-auto h-4 mr-2 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                                                        <path
                                                                                d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"
                                                                                ></path>
                                                                        </svg>
                                                                    </span>
                                                                    Linkedin
                                                                    </a>
                                                                </li>

                                                                <li className="p-0 m-0">
                                                                    <a href={`https://calendar.google.com/calendar/r/eventedit?text=${this.state.event.name}&dates=${ this.state.event.gcal_start_date }/${ this.state.event.gcal_end_date }&details=For+details,+link+here:+${ window.location.href }&location=${ this.state.event.venue_name }+${ this.state.event.venue_address_1 }+${ this.state.event.venue_city }, ${ this.state.event.venue_state } ${ this.state.event.venue_zip } ${ this.state.event.venue_country }`}   target="_blank" rel="noreferrer" className="inline-flex items-center mr-1 px-3 py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-sm text-gray-600 hover:text-gray-800 no-underline" title="Add to Calendar">
                                                                        <span>                                                                    
                                                                            <svg className="w-auto h-4 mr-2 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M96 32C96 14.33 110.3 0 128 0C145.7 0 160 14.33 160 32V64H288V32C288 14.33 302.3 0 320 0C337.7 0 352 14.33 352 32V64H400C426.5 64 448 85.49 448 112V160H0V112C0 85.49 21.49 64 48 64H96V32zM448 464C448 490.5 426.5 512 400 512H48C21.49 512 0 490.5 0 464V192H448V464z"/></svg>
                                                                        </span>
                                                                        Calendar
                                                                    </a>
                                                                </li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                    <p className="text-3xl font-medium text-gray-600 mt-8 md:mt-10"></p>
                                
                                                    <div className="flex items-center flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6 lg:space-x-8 mt-8 md:mt-16">
                                                        {this.state.event.ticket_url &&
                                                            <a itemprop="url" href={this.state.event.ticket_url} target="_blank" rel="noreferrer" className="block w-full md:w-3/5 border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:bg-gray-600 hover:text-white no-underline text-center">
                                                                Get Tickets
                                                            </a>
                                                        }

                                                        {this.state.event.source_url &&
                                                            <a href={this.state.event.source_url} target="_blank" rel="noreferrer" className="block w-full md:w-2/5 border border-gray-800 text-base font-medium leading-none text-gray-800 uppercase py-6 bg-transparent focus:outline-none focus:ring-2 focus:ring-offset-2  focus:ring-gray-800 hover:bg-gray-600 hover:text-gray-600 no-underline text-center">
                                                                More Information
                                                            </a>
                                                        }
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="container mx-auto flex justify-center items-center pt-4 pb-4 border border-solid border-zinc-300 mt-12 bg-slate-100">
                                        <div className="flex flex-col w-3/4 justify-center items-center" itemprop="location" itemscope itemtype="https://schema.org/Place">
                                            
                                            <h3 className="mt-1 text-2xl font-semibold text-center text-gray-800 text-center md:w-9/12 lg:w-7/12 mb-1 pb-1">
                                                Venue Information
                                            </h3>

                                            <h4 className="mt-1 text-xl font-semibold text-center text-gray-800 text-center md:w-9/12 lg:w-7/12 mb-1 pb-1 bg-transparent" itemprop="name">
                                                { this.state.event.venue_name }
                                            </h4>
                                            
                                            <p className="text-base leading-normal text-center text-gray-600 md:w-9/12 lg:w-7/12 mb-4" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                                                <span itemprop="streetAddress">{ this.state.event.venue_address_1 }</span><br />
                                                {this.state.event.venue_address_2 &&
                                                    <>
                                                        { this.state.event.venue_address_2 }<br />
                                                    </>
                                                }
                                                <span itemprop="addressLocality">{ this.state.event.venue_city }</span>,
                                                <span itemprop="addressRegion"> { this.state.event.venue_state }</span> 
                                                <span itemprop="postalCode"> { this.state.event.venue_zip } </span> 
                                                { this.state.event.venue_country }
                                           </p>       

                                            <div className="text-base leading-normal text-center text-gray-600 md:w-9/12 lg:w-7/12 mb-4">
                                                <a href={`https://www.google.com/maps/dir/?api=1&destination=${ this.state.event.venue_name }%20${ this.state.event.venue_address_1 },%20${ this.state.event.venue_city }%20${this.state.event.venue_state}%20${this.state.event.venue_zip}`} rel="noreferrer" target="_blank" className="inline-block w-2/5 border border-gray-800 text-base font-medium leading-none text-gray-800 uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 text-white hover:text-white hover:bg-gray-600 no-underline text-center mt-4 mr-2 pl-2 pr-2">
                                                    Get Directions
                                                </a>

                                                <Link to={`/venue/${ this.state.event.venue_uuid }`} className="inline-block w-2/5 border border-gray-800 text-base font-medium leading-none text-gray-800 uppercase py-6 bg-transparent focus:outline-none focus:ring-2 focus:ring-offset-2  focus:ring-gray-800 hover:bg-gray-600 hover:text-gray-600 no-underline text-center mt-4 pl-2 pr-2">
                                                    Venue's Events
                                                </Link>
                                            </div>

                                            { this.state.event.latitude &&
                                                <MapOutput storageid={ this.state.event.id } latitude={ this.state.event.latitude } longitude={this.state.event.longitude} />
                                            }                                                                             
                                        </div>
                                    </div>

                                    <RelatedEvents parent_id={ this.state.event.id} flags={ this.state.event.flags } />

                                </> 
                            )
                        }
                    }
                })()}
            </>
        );

    }

}

export default EventSingle;