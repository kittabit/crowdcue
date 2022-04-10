import React from 'react';
import { useParams } from "react-router";
import CategoryOutput from '../Components/CategoryOutput';
import OGUserLogging from '../Components/Tools/OGUserLogging';

function SingleCategory() {

    const { slug } = useParams();

    OGUserLogging(slug);

    return (
        <>  
    
            <CategoryOutput slug={slug} />

        </>
    );
    
}

export default SingleCategory;