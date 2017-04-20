var map;
var utrecht;



function initMap() {
  var mapDiv = document.getElementById('map');
  var myOptions = {
    center: {lat: 52.0842715, lng: 5.0124518},
    zoom: 6
  }
  map = new google.maps.Map(mapDiv, myOptions);


  var markers = [];
  var mcOptions = {gridSize: 30, maxZoom: 15, imagePath: 'img/m', minimumClusterSize: 3};
  var markerCluster = new MarkerClusterer(map, markers, mcOptions);
  
  console.log("Max count: " + data.count);
  for (var i = 0; i <= data.count; i++) {
    var dataObjects = data.objects[i];
    var latLng;
    var address = dataObjects.object_adres + " " + dataObjects.object_postcode + " " + dataObjects.object_plaats;
    

    //getAddr(address, function(status, results) {
    //  if (status == "ok" && results[0].geometry.location != undefined) {

    Geocode(address, function(status, results) {
      if (status == "ok" && results[0].geometry.location != undefined) {

          address = results[0].address_components[1].short_name + " " + results[0].address_components[0].short_name;
          //console.log(results[0]);
          //console.log(data.objects[i]);

          var marker = new google.maps.Marker({
            position: results[0].geometry.location,
            map: map,
            title: address
          });

          var content = '<div class="infoWindow">'+
                    '<h2>'+ address +'</h2>'+
                    //'<img src="'+ dataObjects.photo_file_url +'" width="250"/>'+
                    '<h3>'+ results[0].address_components[7].short_name +', ' + results[0].address_components[4].short_name + '</h3>'+
                    //'<p>'+ results[0].address_components[4].short_name +'</p>'+
                    //'<p><a href="'+ dataObjects.photo_url +'" target="_blank"/>Website</a></p>'+
                  '</div>';   
    

          var infowindow = new google.maps.InfoWindow();

          google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
              return function() {
                 infowindow.setContent(content);
                 infowindow.open(map,marker);
              };
          })(marker,content,infowindow));

          markers.push(marker);
          markerCluster.addMarker(marker);

        } else {
          //alert('Geocode was not successful for ' + address + ', by the following reason: ' + status);
          console.log('Geocode was not successful for ' + address + ', by the following reason: ' + status);
        }

    });
  } // end for loop


  utrecht = new google.maps.LatLng(52.0728775,5.1271216);
  var YouAreHere = new google.maps.InfoWindow({map: map});

  // Try HTML5 geolocation.
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var pos = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };

      YouAreHere.setPosition(pos);
      YouAreHere.setContent('You are here');
      map.setCenter(pos);

      // Create a DIV to hold the control and call HomeControl()
      var uiControlDiv = document.createElement('div');
      var uiControl = new UiControl(uiControlDiv, map);
      //  homeControlDiv.index = 1;
      map.controls[google.maps.ControlPosition.TOP_RIGHT].push(uiControlDiv);


    }, function() {
      handleLocationError(true, infoWindow, map.getCenter());
    });
  } else {
    // Browser doesn't support Geolocation
    handleLocationError(false, infoWindow, map.getCenter());
  }
}

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
  infoWindow.setPosition(pos);
  infoWindow.setContent(browserHasGeolocation ?
                        'Error: The Geolocation service failed.' :
                        'Error: Your browser doesn\'t support geolocation.');
}




// Add a Home control that returns the user to Utrecht
function UiControl(controlDiv, map) {
  controlDiv.style.padding = '5px';
  var controlUI = document.createElement('div');
  controlUI.style.backgroundColor = 'yellow';
  controlUI.style.border='1px solid';
  controlUI.style.cursor = 'pointer';
  controlUI.style.textAlign = 'center';
  controlUI.title = 'Set map to Utrecht';
  controlDiv.appendChild(controlUI);
  var controlText = document.createElement('div');
  controlText.style.fontFamily='Arial,sans-serif';
  controlText.style.fontSize='12px';
  controlText.style.paddingLeft = '4px';
  controlText.style.paddingRight = '4px';
  controlText.innerHTML = '<b>Utrecht<b>'
  controlUI.appendChild(controlText);

  // Setup click-event listener: simply set the map to London
  google.maps.event.addDomListener(controlUI, 'click', function() {
    map.setCenter(utrecht);
    smoothZoom(map, 12, map.getZoom());
  });
}

// the smooth zoom function
function smoothZoom (map, max, cnt) {
    if (cnt >= max) {
        return;
    }
    else {
        z = google.maps.event.addListener(map, 'zoom_changed', function(event){
            google.maps.event.removeListener(z);
            smoothZoom(map, max, cnt + 1);
        });
        setTimeout(function(){map.setZoom(cnt)}, 80); // 80ms is what I found to work well on my system -- it might not work well on all systems
    }
} 

getAddr = function(addr, f){
    
  if(typeof addr != 'undefined' && addr != null) {
      var geocoder = new google.maps.Geocoder();

      geocoder.geocode( { address: addr, }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          f('ok', results);
          //console.log("getAddr for: " + addr + " [OK]");
        } else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {    
          setTimeout(function() {
              //getAddr = function(addr, f){}
              //console.log("getAddr for: " + addr + " [" + status + "]");
          }, 200);
        } else {
          f('error', null);
          //console.log("getAddr for: " + addr + " [" + status + "]");
        }
      });
  } else {
    f('error', null);
  }
}

function Geocode(addr, f) {
  var geocoder = new google.maps.Geocoder();
  geocoder.geocode({'address': addr }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
          //console.log("getAddr for: " + addr + " [OK]");
          f('ok', results);
      } else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {    
          setTimeout(function() {
              Geocode(addr, f);
          }, 100);
      } else {
          //console.log("getAddr for: " + addr + " [" + status + "]");
          f('error', null);
        }
  });
}

function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}