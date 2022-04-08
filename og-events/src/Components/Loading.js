import React from 'react';

class Loading extends React.Component {
    
    render() {

        return ( 
            <>
            
                <div className="col-span-12 text-center mb-12">
                    <div className="w-12 h-12 rounded-full animate-spin border-2 border-solid border-gray-800 border-t-transparent ml-auto mr-auto"></div>
                </div>
    
            </>

        );

    }

}

export default Loading;