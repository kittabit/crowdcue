import React from 'react';
import { Link } from 'react-router-dom';

function EventGridItem(props) {

    let eventClassName = "col-span-12 md:col-span-6 lg:col-span-3 bg-slate-100 rounded-lg h-auto md:h[115px] lg:h[100px] no-underline pb-10 relative mb-2";
    if( props.largerGrid === "1"){
        eventClassName = "col-span-12 md:col-span-6 lg:col-span-6 bg-slate-100 rounded-lg h-auto md:h[115px] lg:h[100px] no-underline pb-10 relative mb-2";
    }

    return (
        <>  
    
            <div className={ eventClassName }>
                <Link to={`/details/${ props.item.slug }/`} className="no-underline">
                    <img src={ props.item.image_url } alt={ props.item.name } className="rounded-t-lg h-auto md:max-h-44 w-full" loading="lazy" />
                    <p className="text text-gray-600 pt-3 pl-3 pr-3 no-underline text-ellipsis ... overflow-hidden line-clamp-2 h-[4.5rem] pb-1 mb-0"> { props.item.name } </p>
                    <p className="text font-light text-gray-600 pt-0 pl-3 pr-3 pb-2 mb-0 no-underline text-sm"> 
                        <span className="text-xs">{ props.item.date_formatted }</span><br />
                        <span className="text-xs">
                            <svg className="w-2 inline-block fill-inherit" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg>    
                            <span className="inline-block pl-2">{ props.item.venue_city }, { props.item.venue_state }</span>
                        </span>
                    </p>
                    <span className="absolute text-sm font-light decoration-white underline text-white text-center block mt-4 underline-offset-4 -bottom-2 w-full bg-slate-100 bg-slate-400 py-2 rounded-xl">
                        More Info
                        <svg className="w-6 inline-block pl-2 text-inherit fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504.3 273.6l-112.1 104c-6.992 6.484-17.18 8.218-25.94 4.406c-8.758-3.812-14.42-12.45-14.42-21.1L351.9 288H32C14.33 288 .0002 273.7 .0002 255.1S14.33 224 32 224h319.9l0-72c0-9.547 5.66-18.19 14.42-22c8.754-3.809 18.95-2.075 25.94 4.41l112.1 104C514.6 247.9 514.6 264.1 504.3 273.6z"/></svg>
                    </span>
                </Link>
            </div>

        </>
    );
    
}

export default EventGridItem;