function OGUserLogging( category=null, event=null, venue=null ) {

    if(localStorage.getItem('og_user_flags') !== null){
        var og_user_flags = JSON.parse(localStorage.getItem('og_user_flags'));
    }
    
    if(localStorage.getItem('og_user_events') !== null){
        var og_user_events = JSON.parse(localStorage.getItem('og_user_events'));
    }    

    if(localStorage.getItem('og_user_venues') !== null){
        var og_user_venues = JSON.parse(localStorage.getItem('og_user_venues'));
    }        

    if(category){
        if(og_user_flags){
            og_user_flags.push( category );
        }else{
            og_user_flags = [ category ];
        }
        localStorage.setItem('og_user_flags', JSON.stringify(og_user_flags));
    }

    if(event){
        if(og_user_events){
            og_user_events.push( event );
        }else{
            og_user_events = [ event ];
        }
        localStorage.setItem('og_user_events', JSON.stringify(og_user_events));
    }

    if(venue){
        if(og_user_venues){
            og_user_venues.push( venue );
        }else{
            og_user_venues = [ venue ];
        }
        localStorage.setItem('og_user_venues', JSON.stringify(og_user_venues));
    }
        
}

export default OGUserLogging;