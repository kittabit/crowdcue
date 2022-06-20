import React from 'react';
import Loading from './Loading';

class EventFilter extends React.Component {
    
    constructor (props){

        super(props);
        this.state = {
            categories: [],
            areas: [],
            isLoading: 1
        }
        
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

    render() {

        return ( 
            
            <>
                <form onSubmit={this.props.handleSubmit}>
                    <div className="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">Start Date</label>
                        <input onChange={this.props.handleStartDate} type="date" name="filter_start_date" id="filter_start_date" className="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" min={ window.ogSettings.og_base_date } value={ this.props.start_date } />
                    </div>

                    <div className="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">End Date</label>
                        <input onChange={this.props.handleEndDate} type="date" name="filter_end_date" id="filter_end_date" className="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" min={ this.props.min_date } max={ this.props.max_date } value={ this.props.end_date } />
                    </div>

                    <div class="w-11/12 mb-4">
                        <label className="text-gray-700 text-sm font-medium">Categories</label>

                        {this.state.isLoading ? (
                            <Loading />
                        ) : (
                            <>
                            <select onChange={this.props.handleCategories} name="filter_categories" id="filter_categories" class="form-multiselect block w-full mt-1 text-sm overscroll-auto" multiple>
                                <option value="" selected>All Categories</option>
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
                            <select onChange={this.props.handleAreas} name="filter_areas" id="filter_areas" class="form-multiselect block w-full mt-1 text-sm overscroll-auto" multiple>
                                <option value="" selected>All Areas</option>
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
                        <button onClick={this.props.handleFilter} className="block w-full border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:bg-gray-600 hover:text-white no-underline text-center">Filter</button>
                    </div>               
                </form>
            </>

        );

    }

}

export default EventFilter;