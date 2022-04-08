import React from 'react';
import { useParams } from "react-router";
import VenueOutput from '../Components/VenueOutput';

function SingleVenue() {

    const { uuid } = useParams();

    return (
        <>  
    
            <VenueOutput uuid={uuid} />

        </>
    );
    
}

export default SingleVenue;