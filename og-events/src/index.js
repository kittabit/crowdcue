import React from 'react';
import ReactDOM from 'react-dom';
import Event from "./event"
import './index.css';

class App extends React.Component {

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

    const { isLoading, events, current_page, next_page, max_pages } = this.state;

    return (  
      <div className="occasiongenius-parent-container">

        {this.state.isLoading ? (
          <div className="occassiongenius-loaded">{this.state.loadingText}</div>
        ) : (
          <div className="occassiongenius-loaded">
            <div className="occasiongenius-container"> 
              {this.state.events.map((item, index) => (            
                  <Event data={item}  />
              ))}
            </div> 

            <div className="occasiongenius-pagination">
                {current_page > 1 &&
                  <button onClick={this.prevPage}>Previous Page</button>
                }

                <p>Page {current_page} of {max_pages}</p>

                {next_page < max_pages &&
                  <button onClick={this.nextPage}>Next Page</button>
                }
            </div>
          </div>
        )}

      </div>
    );

  }

}

const targets = document.querySelectorAll('.og-root');
Array.prototype.forEach.call(targets, target => {
  ReactDOM.render(React.createElement(App, null), target);
});