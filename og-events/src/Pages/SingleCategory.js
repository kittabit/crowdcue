import React from 'react';
import { useParams } from "react-router";
import CategoryOutput from '../Components/CategoryOutput';

function SingleCategory() {

    const { slug } = useParams();

    return (
        <>  
    
            <CategoryOutput slug={slug} />

        </>
    );
    
}

export default SingleCategory;