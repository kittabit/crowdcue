import React from 'react';
import { useParams } from "react-router";
import VenueOutput from '../Components/VenueOutput';
import OGUserLogging from '../Components/Tools/OGUserLogging';

function SingleVenue() {

    const { uuid } = useParams();
    OGUserLogging("", "", uuid );

    return (
        <>  
    
            <VenueOutput uuid={uuid} />

        </>
    );
    
}

export default SingleVenue;