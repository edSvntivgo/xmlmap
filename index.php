<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/range.css">
<style type="text/css">
		#map{
			overflow: hidden;
		    width: 100%;
		    height: 100%;
		    position: absolute;
		}
</style>
</head>
<body style="margin:0px; padding:0px;">
	<div id="capa">
		<input id="pac-input" class="controls form-control" type="text" placeholder="Busqueda de Anuncios">		
		          <div class="range range-primary">
		            <input id="radiusSelect" type="range" name="range" min="3" max="200" value="3" onchange="searchLocations()" >
		            <output id="range">Radio en KM</output>
		          </div>
	</div>
    <div>
    	<select id="locationSelect" style="width:100%;visibility:hidden"></select>
    </div>
    <div id="map" ></div>

 	<script src="https://code.jquery.com/jquery-3.1.1.js" integrity="sha256-16cdPddA6VdVInumRGo6IbivbERE8p7CQR3HzTBuELA="
  	crossorigin="anonymous"></script>
   	<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyA7NWXEzJ-RRutuDhaKuFTbVB9pnixS-j8&libraries=places&callback=initAutocomplete" async defer></script>
	<script src="js/markerclusterer.js"></script>
<script type="text/javascript">
 	var map;
    var markers = new Array();
    var infoWindow;
    var locationSelect;

	function initAutocomplete() {
	   		map = new google.maps.Map(document.getElementById("map"), {
			        center: new google.maps.LatLng(34.0201812, -118.6919188),
			        zoom: 4,
			        mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
	      	});
	      infoWindow = new google.maps.InfoWindow();

	      locationSelect = document.getElementById("locationSelect");
	      	locationSelect.onchange = function() {
	        	var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
			        if (markerNum != "none"){
			          	google.maps.event.trigger(markers[markerNum], 'click');
			        }
	      	};
	     var input = document.getElementById('pac-input');
	     var searchBox = new google.maps.places.SearchBox(input);
	      map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('capa')); 
	      map.addListener('bounds_changed', function() {
	          searchBox.setBounds(map.getBounds());
	      });

	      searchBox.addListener('places_changed', function() {
	        var places = searchBox.getPlaces();
	          if (places.length == 0) {
	            return
	          }          
	          var bounds = new google.maps.LatLngBounds();
	          places.forEach(function(place) {
	            if (place.geometry.viewport) {
	              bounds.union(place.geometry.viewport);
	            } else {
	              bounds.extend(place.geometry.location);
	            }
	          });
	          map.fitBounds(bounds);
	      });    
	}

	function searchLocations() {
	     var address = document.getElementById("pac-input").value;
	     var geocoder = new google.maps.Geocoder();
	     	geocoder.geocode({address: address}, function(results, status) {
			       if (status == google.maps.GeocoderStatus.OK) {
			        	searchLocationsNear(results[0].geometry.location,map);
			       } else {
			         	alert(address + 'Escribe direccion');
			       }
	     	});
	}

	function clearLocations() {
	     infoWindow.close();
	     for (var i = 0; i < markers.length; i++) {
	       		markers[i].setMap(null);
	     }
	     markers.length = 0;

	     locationSelect.innerHTML = "";
	     var option = document.createElement("option");
		     option.value = "none";
		     option.innerHTML = "Verifica los resultados:";
	     locationSelect.appendChild(option);
	}

	function searchLocationsNear(center) {
	     clearLocations();

	     var radius = document.getElementById('radiusSelect').value;
	     var searchUrl = 'xmlcreate.php?latitud=' + center.lat() + '&longitud=' + center.lng() + '&radius=' + radius;
	     	downloadUrl(searchUrl, cargarpines);
	}
	function cargarpines(data) {
		       var xml = parseXml(data);
		       var markerNodes = xml.documentElement.getElementsByTagName("os_anuncios");
		       if(markerNodes.length==0){
		       		alert('no hay marcadores en la zona');
		       }
		       var bounds = new google.maps.LatLngBounds();
			       for (var i = 0; i < markerNodes.length; i++) {
				         var name = markerNodes[i].getAttribute("nombre");
				         var input = markerNodes[i].getAttribute("delegacion");
				         var distance = parseFloat(markerNodes[i].getAttribute("distance"));
				         var latlng = new google.maps.LatLng(
					              parseFloat(markerNodes[i].getAttribute("latitud")),
					              parseFloat(markerNodes[i].getAttribute("longitud")));
				        createMarker(latlng, name, input);
				        bounds.extend(latlng);
	       			}
		       	locationSelect.style.visibility = "visible";
		       	locationSelect.onchange = function() {
			         var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
			         google.maps.event.trigger(markers[markerNum], 'click');
		       		};
	}
	function createMarker(latlng, name, input) {
	      var html = "<b>" + name + "</b> <br/>" + input;
	      var img='img/pin.png';
	      var marker = new google.maps.Marker({
		        map: map,
		        position: latlng,
		        icon:img
	      });
	      if(marker==0){
	      	alert('no hay Anuncios en la zona');
	      }
	     	google.maps.event.addListener(marker, 'click', function() {
		        infoWindow.setContent(html);
		        infoWindow.open(map, marker);
		        setMapOnAll(null);
	      	});
	      markers.push(marker);
	}

	function createOption(name, distance, num) {
	      var option = document.createElement("option");
		      option.value = num;
		      option.innerHTML = name + "(" + distance.toFixed(1) + ")";
	      locationSelect.appendChild(option);
	}

	function downloadUrl(url, callback) {
	      var request = window.ActiveXObject ?
	          new ActiveXObject('Microsoft.XMLHTTP') :
	          new XMLHttpRequest;

	      request.onreadystatechange = function() {
	        if (request.readyState == 4) {
	          request.onreadystatechange = doNothing;
	          callback(request.responseText, request.status);

	        }
	      };
	      request.open('GET', url, true);
	      request.send(null);
	}

	function parseXml(str) {
	      if (window.ActiveXObject) {
	        var doc = new ActiveXObject('Microsoft.XMLDOM');
	        doc.loadXML(str);
	        return doc;
	      } else if (window.DOMParser) {
	        return (new DOMParser).parseFromString(str, 'text/xml');
	      }
	}
	function doNothing() {}
</script>	
</body>
</html>