import React, { Component } from 'react'
import Event from "../Components/event"
import OGHeading from "../Components/ogheading"
import '../index.css';

class CategoryArchive extends Component {

    constructor (props){

        super(props);
        this.state = {
          current_page: [],
          next_page: [],
          max_pages: [],
          events: [],
          isLoading: 1,
          events_url: "/wp-json/occasiongenius/v1/events?page=",
          loadingText: "Loading Current Events..."
        }
        
    }
    
    componentDidMount() {

        Promise.all([
          fetch('/wp-json/occasiongenius/v1/events'),
        ])
        .then(([res1]) => Promise.all([res1.json()]))
        .then(([data1]) => this.setState({
          events: data1.events,
          current_page: data1.info.current_page, 
          next_page: data1.info.next_page, 
          max_pages: data1.info.max_pages, 
          isLoading: 0
        }));

        if(this.props.number){
          this.nextPage();
          this.setState({
            current_page: this.props.number
          });
        }

        document.title = "Local Events";

    } 
    
    handleEvent(){
    
        console.log(this.props);  
    
    }     
    
    fetchData = async (url) => {
    
        await fetch(url)
          .then((r) => r.json())
          .then((result) => {
            this.setState({
              events: result.events,
              isLoading: 0
            });
          })
          .catch((e) => {
            console.log(e);
          });
    
    };  
    
    nextPage = () => {
    
        this.setState({
            current_page: this.state.current_page + 1,
            isLoading: 1,
            loadingText: "Loading Next Page of Events..."
          },
          () => {
            const events_url = this.state.events_url + this.state.current_page;
            this.fetchData(events_url);
            window.scrollTo({
              top: 0,
              behavior: "smooth"
            });
          }
        );
    
    };
    
    prevPage = () => {
    
        this.setState({
            current_page: this.state.current_page - 1,
            isLoading: 1,
            loadingText: "Loading Previous Page of Events..."
          },
          () => {
            const events_url = this.state.events_url + this.state.current_page;
            this.fetchData(events_url);
            window.scrollTo({
              top: 0,
              behavior: "smooth"
            });
          }
        );
    
    };

    render(){

        const { current_page, next_page, max_pages } = this.state;

        return (
            <>
            
                {this.state.isLoading ? (
                  <div className="occassiongenius-loaded">
                    <OGHeading />
                    
                    <div className="occasiongenius-container occasiongenius-non-grid"> 
                      <div className="occassiongenius-loaded">{this.state.loadingText}</div>
                    </div>
                    
                  </div>
                ) : (
                    <div className="occassiongenius-loaded">
                        <OGHeading />

                        <div className="occasiongenius-container"> 
                            {this.state.events.map((item, index) => (            
                                <Event data={item}  />
                            ))}
                        </div> 

                        <div className="occasiongenius-pagination">
                            {current_page > 1 &&
                                <>
                                    <button onClick={this.prevPage}>Previous Page</button>
                                </>
                            }
                            {next_page < max_pages &&
                                <>
                                  <button onClick={this.nextPage}>Next Page</button>
                                </>                                
                            }
                        </div>
                    </div>
                )}

            </>
        )
    }
}

export default CategoryArchive
