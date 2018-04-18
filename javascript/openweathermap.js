$("#google_map_1").on("mapInitialised", function ( event, map ) {
    console.log(map);
    var forecastioinfowindow = new google.maps.InfoWindow({});

    google.maps.event.addListener(map, 'rightclick', function (event) {
        console.log('right click on map');
        var lat = event.latLng.lat();
        var lng = event.latLng.lng();
        console.log("Lat=" + lat + "; Lng=" + lng);

        // http://forecast.io/#/f/13.8607,100.5148
        var linkHTML = 'View weather at '+lat+','+lng+'<br/>';
        linkHTML = linkHTML + '<a target="_weather" href="http://forecast.io/#/f/'+lat+','+lng+'">forecast.io</a>'

        forecastioinfowindow.setContent(linkHTML);
        var position = new google.maps.LatLng(lat,lng);
        console.log('Position',position.lat(), position.lng());

        forecastioinfowindow.setPosition(position);
        forecastioinfowindow.open(map);
    });
});


/*

*/
