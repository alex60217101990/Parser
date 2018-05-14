import {Component, ElementRef, NgZone, OnInit, ViewChild} from '@angular/core';
import {GoogleMapService} from './google-map.service';
import {FormControl, Validators} from '@angular/forms';
import { } from 'googlemaps';
import {AgmMap, AgmMarker, MapsAPILoader} from '@agm/core';
import {concat} from 'rxjs/observable/concat';
declare var google: any;
@Component({
  selector: 'app-google-map',
  templateUrl: './google-map.component.html',
  styleUrls: ['./google-map.component.scss']
})
export class GoogleMapComponent implements OnInit {
    lat: number;
    lng: number;
    zoom: number;

    protected type: string;
    public searchControl: FormControl;
    protected address: string;
    protected typeMap: string;
    protected fullscreenControl: boolean = true;
    protected zoomControl: boolean = true;
    protected streetViewControl: boolean = true;

    @ViewChild("search")
    public searchElementRef: ElementRef;

    @ViewChild("marker")
    public MarkerRef: AgmMarker;

    @ViewChild("map")
    public Map: AgmMap;

  constructor(private mapsAPILoader: MapsAPILoader, private __zone: NgZone ) {
      /*сделать подписку на получение экземпляра объявления при инициализации и вывода в поле адреса.*/
  }

  ngOnInit() {
      //set google maps defaults
      this.zoom = 12;
      this.lat = 49.98611063781005;
      this.lng = 36.235313415527344;

      this.typeMap = "roadmap";


      //create search FormControl
      this.searchControl = new FormControl();

      //set current position
      this.setCurrentPosition();


      //load Places Autocomplete
      this.mapsAPILoader.load().then(() => {
          let autocomplete = new google.maps.places.Autocomplete(this.searchElementRef.nativeElement, {
              types: ["address"],
          });

          this.resizeMap();

          this.geocoder = new google.maps.Geocoder();

          autocomplete.addListener("place_changed", () => {
              this.__zone.run(() => {
                  //get the place result
                  let place: google.maps.places.PlaceResult = autocomplete.getPlace();
                  //verify result
                  if (place.geometry === undefined || place.geometry === null) {
                      return;
                  }

                  //set latitude, longitude and zoom
                  this.lat = place.geometry.location.lat();
                  this.lng = place.geometry.location.lng();
                  this.markers[0].lat = place.geometry.location.lat();
                  this.markers[0].lng = place.geometry.location.lng();
                  this.zoom = 12;
                  this.codeLatLng(place.geometry.location.lat(), place.geometry.location.lng());
              });
          });

      });
  }

    private setCurrentPosition() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                this.lat = position.coords.latitude;
                this.lng = position.coords.longitude;
                this.zoom = 12;
            });
        }
    }

    ready(){
       // this.getAddress();
        console.log(this.lat);
    }

    mapClicked($event: MouseEvent){
        this.markers[0]={
            lat: $event['coords'].lat,
            lng: $event['coords'].lng,
            label: 'A',
            draggable: true
        };
        this.codeLatLng($event['coords'].lat, $event['coords'].lng);
    }

    markerDragEnd(m: marker, $event: MouseEvent) {
        m.lat = $event['coords'].lat;
        m.lng = $event['coords'].lng;
        //console.log('dragEnd', m);
        this.codeLatLng($event['coords'].lat, $event['coords'].lng);
    }

    protected markers: marker[] = [
        {
            lat: 50.0,
            lng: 36.15,
            label: 'A',
            draggable: true
        }
    ];

  private geocoder;
    private codeLatLng(lat, lng) {
        var latlng = new google.maps.LatLng(lat, lng);

        this.geocoder.geocode({
            'latLng': latlng
        },(results, status)=>{
            if (status === google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    console.log(results[0]['formatted_address']);
                    this.address = results[0]['formatted_address'];
                } else {
                    console.log('No results found');
                }
            } else {
                console.log('Geocoder failed due to: ' + status);
            }
        });
    }
    static unique(arr) {
        var obj = {};

        for (var i = 0; i < arr.length; i++) {
            var str = arr[i];
            obj[str] = true; // запомнить строку в виде свойства объекта
        }

        return Object.keys(obj); // или собрать ключи перебором для IE8-
    }

    ngAfterViewInit() {
        //this.MarkerRef.setAnimation(google.maps.Animation.BOUNCE);
        this.resizeMap();
    }

    resizeMap() {
        this.Map.triggerResize();
    }

}

// just an interface for type safety.
interface marker {
    lat: number;
    lng: number;
    label?: string;
    draggable: boolean;
}