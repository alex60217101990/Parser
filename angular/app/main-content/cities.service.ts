import {Injectable, OnDestroy} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs/Observable';
import {element} from 'protractor';
import {AdDataMenedgerService} from '../update-ad/ad-data-menedger.service';

export interface Ad {
    id: number;
    link: string;
    updated_at: Date;
    ad_from: string;
    type_of_structure: string;
    type_of_apartment: string;
    number_of_rooms: number;
    floor: number;
    storey_house: number;
    total_area: number;
    living_space: number;
    kitchen_area: number;
    price: number;
    telephones: string;
    number_of_photos: number;
    status: string;
    number_of_similar: number;

    address: string;
    cause_of_change: string;
    exchange: boolean;
    formula_of_exchange: string;
    state: string;
    source_of_information: string;
    bathroom_type: string;
    wall_material: string;
    phone_line: boolean;
    having_a_bath: boolean;
    number_of_balconies: number;
    notes: string;
    display_info: string;
}

@Injectable()
export class CitiesService implements OnDestroy{
private Ads: Array<Ad>;
private ads: Observable<Array<Ad>>;
  constructor(private http: HttpClient, private DataService: AdDataMenedgerService) {
      this.Ads = new Array<Ad>();
      console.log(this.Ads.length);
      this.ads = this.DataService.Ads;
      this.DataService.loadAll();    // load all ads.
  }

    ngOnDestroy() {
       // this.Ads.length = 0;
    }

    /**
     * Method for get all aps.
     * @param {void}
     * @return {Observable<Array<Ad>>}
     */

  public getCities():Observable<Array<Ad>>{
      return this.ads;
  }

  public uploadPhoto(formdata: FormData):Observable<any>{
     return this.http.post('/content/uploadAvatar', formdata)
          .map(response=>{
          return response;
          })
  }

  public uploadAdImages(formdata: FormData):Observable<any>{
      return this.http.post('/content/uploadAdImages', formdata)
          .map(response=>{
              return response;
          });
  }


  public getApPhotos(id: number): Observable<any>{
      return this.http.post('/content/getAppPhotos', {id: id})
          .map(response => {
             if(response['images'] instanceof Array && response['ad'] instanceof Object &&
                !!response['ad']){
                 return response;
             }
          });
  }

    /**
     * Method for save Global URL of avatar image.
     * @param {string} url
     * @return {Observable<any>}
     */
  public saveAvatarUrl(url: string):Observable<any>{
      return this.http.post('/content/saveAvatarGlobalUrl', {url: url})
          .map(response => {return response;});
  }

    /**
     * Method for save Global URL of Ad image.
     * @param {string} url
     * @return {Observable<any>}
     */
  public saveAdUrlImage(url: string, id: number): Observable<any>{
      return this.http.post('/content/saveAdImgGlobalUrl', {url: url, ad: id})
          .map(response => {return response;});
  }

  public deleteAdImage(id: number):Observable<any> {
      return this.http.request('delete', '/content/deleteAdImage', { body: {id: id} })
          .map(response => {return response;});
  }

    //disconnect(): void {};
}


