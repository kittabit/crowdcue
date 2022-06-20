import React from 'react';
import GoogleMapReact from 'google-map-react';
import { Link } from 'react-router-dom';

const OGMarker = props => {
    if(props.type === "nearby"){
        return <>
        <div className="gmap-pulse">
            <Link to={`/details/${ props.slug }/`} className="no-underline">
                <svg className="w-6 h-6 fill-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg>
            </Link>
        </div>
        </>
    }else{
        return <>
        <div className="gmap-pulse">
            <svg className="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M320 144C320 223.5 255.5 288 176 288C96.47 288 32 223.5 32 144C32 64.47 96.47 0 176 0C255.5 0 320 64.47 320 144zM192 64C192 55.16 184.8 48 176 48C122.1 48 80 90.98 80 144C80 152.8 87.16 160 96 160C104.8 160 112 152.8 112 144C112 108.7 140.7 80 176 80C184.8 80 192 72.84 192 64zM144 480V317.1C154.4 319 165.1 319.1 176 319.1C186.9 319.1 197.6 319 208 317.1V480C208 497.7 193.7 512 176 512C158.3 512 144 497.7 144 480z"/></svg>
        </div>
        </>
    }
}

class MapOutput extends React.Component {
     
    constructor (props){

        super(props);
        this.state = {
            nearby: [],
            latitude: "",
            longitude: "",
            center: { lat: Number(this.props.latitude), lng: Number(this.props.longitude) }
        }
        
    }

    componentDidMount() {

        this.setState({
            latitude: this.props.latitude,
            longitude: this.props.longitude,
            center: { lat: Number(this.props.latitude), lng: Number(this.props.longitude) }
        });

        Promise.all([
            fetch('/wp-json/occasiongenius/v1/nearby/' + this.props.storageid)
        ])      
        .then(([res]) => Promise.all([res.json()]))
        .then(([data]) => {                
            this.setState({            
                nearby: data.results            
            })
        });

    }

    componentDidUpdate(previousProps, previousState) {
        
        if (previousProps.latitude !== this.props.latitude && previousProps.longitude !== this.props.longitude) {

            this.setState({
                latitude: this.props.latitude,
                longitude: this.props.longitude,
                center: { lat: Number(this.props.latitude), lng: Number(this.props.longitude) }
            });

            Promise.all([
                fetch('/wp-json/occasiongenius/v1/nearby/' + this.props.storageid)
            ])      
            .then(([res]) => Promise.all([res.json()]))
            .then(([data]) => {                
                this.setState({            
                    nearby: data.results            
                })
            });

            this.render();

        }

    }

    render() { 

        return (
            
            <>

                <div className="w-full h-[350px] mt-4 mb-2" data-lat={ Number(this.state.latitude) } data-lng={ Number(this.state.longitude) }>
                    <GoogleMapReact                        
                        bootstrapURLKeys={{ key: window.ogSettings.og_gmaps_api_key }}
                        defaultCenter={ this.state.center }
                        center={ this.state.center }
                        defaultZoom={14}
                        yesIWantToUseGoogleMapApiInternals
                    >
                        <OGMarker lat={ Number(this.state.latitude) } lng={ Number(this.state.longitude) } />

                        {this.state.nearby.map((item, index) => (   
                            <OGMarker key={index} lat={ Number(item.latitude) } lng={ Number(item.longitude) } type="nearby" slug={ item.slug } />
                        ))}

                    </GoogleMapReact>
                </div>

            </>
        )

    }
    
}

export default MapOutput;