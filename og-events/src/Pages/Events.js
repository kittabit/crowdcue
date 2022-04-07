import React, { Component } from 'react'
import Breadcrumbs from '../Components/Breadcrumbs';
import '../index.css';

class Events extends Component {

    constructor (props){

        super(props);
        this.state = {

        }
        
    }
    
    componentDidMount() {

        // Promise.all([
        //   fetch('/wp-json/occasiongenius/v1/events'),
        // ])
        // .then(([res1]) => Promise.all([res1.json()]))
        // .then(([data1]) => this.setState({
        //   events: data1.events,
        //   current_page: data1.info.current_page, 
        //   next_page: data1.info.next_page, 
        //   max_pages: data1.info.max_pages, 
        //   isLoading: 0
        // }));

        // if(this.props.number){
        //   this.nextPage();
        //   this.setState({
        //     current_page: this.props.number
        //   });
        // }

        // document.title = "Local Events";

    } 
    
    handleEvent(){
    
        console.log(this.props);  
    
    }         

    render(){


        return (
            <>

                <Breadcrumbs page_name="All Events" />
                
                [events_go_here]

            </>
        )
    }
}

export default Events
