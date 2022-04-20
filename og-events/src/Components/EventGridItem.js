import React from 'react';
import { Link } from 'react-router-dom';

function EventGridItem(props) {

    return (
        <>  
    
            <div className="col-span-12 md:col-span-6 lg:col-span-3 bg-slate-100 rounded-lg h-auto md:h[115px] lg:h[100px] no-underline pb-4">
                <Link to={`/events/details/${ props.item.slug }/`} className="no-underline">
                    <img src={ props.item.image_url } alt={ props.item.name } className="rounded-t-lg h-auto md:max-h-44 w-full" loading="lazy" />
                    <p className="text text-gray-600 pt-3 pl-3 pr-3 no-underline text-ellipsis ... overflow-hidden line-clamp-2 h-[4.5rem] pb-1 mb-0"> { props.item.name } </p>
                    <p className="text font-light text-gray-600 pt-0 pl-3 pr-3 pb-0 mb-0 no-underline text-sm"> 
                        { props.item.date_formatted } <br />
                        { props.item.venue_city }, { props.item.venue_state }
                    </p>
                    <span className="text-sm font-light decoration-gray-500 underline text-gray-600 text-center block mt-4 underline-offset-4">More Info</span>
                </Link>
            </div>

        </>
    );
    
}

export default EventGridItem;