import React from 'react';
import ReactDOM from 'react-dom';
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
      events_url: "/wp-json/occasiongenius/v1/events?page="
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
        current_page: this.state.current_page + 1
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
        current_page: this.state.current_page - 1
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

        <div className="occasiongenius-container"> 
          {this.state.events.map((item, index) => (
            <div className="occasiongenius-single-item">
              <div className="occasiongenius-single_image" style={{ backgroundImage: `url(${item.image_url})` }}>
                <img src={ item.image_url } alt={ item.name } title={ item.title } loading="lazy" />
              </div>
              <span className="occasiongenius-single_title">{ item.name }</span>
              <span className="occasiongenius-single_location">{ item.venue_city }, { item.venue_state }</span>
              <span className="occasiongenius-single_date">{ item.start_date }</span>
            </div>
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
    );
  }
}

const targets = document.querySelectorAll('.og-root');
Array.prototype.forEach.call(targets, target => {
  const id = target.dataset.id;
  const settings = window.wcSettings[id];

  ReactDOM.render(React.createElement(App, null), target);
});