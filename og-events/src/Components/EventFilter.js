import React from 'react';
import Loading from './Loading';

class EventFilter extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            categories: [],
            areas: [],
            isLoading: 1,
            start_date: window.ogSettings.og_base_date,
            end_date: '',
            min_date: window.ogSettings.og_min_base_date,
            filter_categories: '',
            filter_areas: ''
        }
        
        this.handleStartDate = this.handleStartDate.bind(this);
        this.handleEndDate = this.handleEndDate.bind(this);
        this.handleCategories = this.handleCategories.bind(this);
        this.handleAreas = this.handleAreas.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }
    
    componentDidMount() {

        Promise.all([
          fetch('/wp-json/occasiongenius/v1/flags'),
          fetch('/wp-json/occasiongenius/v1/areas'),
        ])
        .then(([res, res2]) => Promise.all([res.json(), res2.json()]))
        .then(([data, data2]) => this.setState({
          categories: data,
          areas: data2,
          isLoading: 0
        }));

    } 

    handleStartDate(event) {
        this.setState({
            start_date: event.target.value,
            min_date: event.target.value
        });
    }

    handleEndDate(event) {
        this.setState({
            end_date: event.target.value
        });
    }

    handleCategories = (e) => {
        let value = Array.from(e.target.selectedOptions, option => option.value);
        this.setState({filter_categories: value});
    }

    handleAreas = (e) => {
        let value = Array.from(e.target.selectedOptions, option => option.value);
        this.setState({filter_areas: value});
    }    

    handleSubmit(event) {
        console.log("Start: " + this.state.start_date);
        console.log("End: " + this.state.end_date);
        console.log("Categories: " + this.state.filter_categories);
        console.log("Areas: " + this.state.filter_areas);
        
        var fetch_url = "/wp-json/occasiongenius/v1/events?limit=100&filter_start=" + this.state.start_date + "&filter_end=" + this.state.end_date + "&filter_flags=" + this.state.filter_categories + "&filter_areas=" + this.state.filter_areas;
        console.log(":: Fetch: " + fetch_url);

        this.props.fetchData(fetch_url);
        event.preventDefault();
    }

    render() {

        return ( 
            
            <>
                <form onSubmit={this.handleSubmit}>
                    <div className="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">Start Date</label>
                        <input onChange={this.handleStartDate} type="date" name="filter_start_date" id="filter_start_date" className="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" min={ window.ogSettings.og_base_date } value={ this.state.start_date } />
                    </div>

                    <div className="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">End Date</label>
                        <input onChange={this.handleEndDate} type="date" name="filter_end_date" id="filter_end_date" className="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" min={ this.state.min_date } value={ this.state.end_date } />
                    </div>

                    <div class="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">Categories</label>

                        {this.state.isLoading ? (
                            <Loading />
                        ) : (
                            <>
                            <select onChange={this.handleCategories} name="filter_categories" id="filter_categories" class="form-multiselect block w-full mt-1 text-sm" multiple>
                                {this.state.categories.map((item, index) => (
                                    <>
                                        <option value={ item.slug }>{ item.output }</option>
                                    </>
                                ))}
                            </select>
                            </>
                        )}

                    </div>

                    <div class="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">Areas</label>

                        {this.state.isLoading ? (
                            <Loading />
                        ) : (
                            <>
                            <select onChange={this.handleAreas} name="filter_areas" id="filter_areas" class="form-multiselect block w-full mt-1 text-sm" multiple>
                                {this.state.areas.map((item, index) => (
                                    <>
                                        <option value={ item.slug }>{ item.output }</option>
                                    </>
                                ))}
                            </select>
                            </>
                        )}

                    </div>     

                    <div class="w-11/12">
                        <button onClick={this.handleFilter} className="block w-full border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:text-white no-underline text-center">Filter</button>
                    </div>               
                </form>
            </>

        );

    }

}

export default EventFilter;