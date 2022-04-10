import React from 'react';
import { useParams } from "react-router";
import EventSingle from '../Components/EventSingle';

function EventDetails() {

    const { slug } = useParams();

    return (
        <>  
        
            <div itemscope="" itemtype="https://schema.org/Event">
                <EventSingle uuid={slug} />
            </div>

        </>
    );
    
}

export default EventDetails;