import { Injectable } from '@angular/core';
import { map } from 'rxjs/operators';
import {BehaviorSubject} from 'rxjs/BehaviorSubject';
import {Ad} from '../main-content/cities.service';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs/Observable';

@Injectable()
export class AdDataMenedgerService {
  Ads: Observable<Ad[]>;
  private _ads: BehaviorSubject<Ad[]>;
  private prefix_Url: string;
  private dataStore: {
    // This is where we will store our data in memory
      AdList: Ad[]
  };
  constructor(private http: HttpClient) {
      this.prefix_Url  = '/content';
      this.dataStore = { AdList: [] };
      this._ads = <BehaviorSubject<Ad[]>>new BehaviorSubject([]);
      this.Ads = this._ads.asObservable();
  }

    loadAll() {
        this.http.post(`${this.prefix_Url}/getApsList`,1).subscribe(data => {
            if(!!data) {
                console.log(data);
                for (let element in data) {
                    if (data[element] instanceof Array) {
                        for (let item of data[element]) {
                            this.dataStore.AdList.push({
                                id: item['id'],
                                link: item['link'],
                                updated_at: new Date(item['updated_at']),
                                ad_from: item['ad_from'],
                                type_of_structure: item['type_of_structure'],
                                type_of_apartment: item['type_of_apartment'],
                                number_of_rooms: item['number_of_rooms'],
                                floor: item['floor'],
                                storey_house: item['storey_house'],
                                total_area: item['total_area'],
                                living_space: item['living_space'],
                                kitchen_area: item['kitchen_area'],
                                price: item['price'],
                                telephones: item['telephones'],
                                number_of_photos: item['number_of_photos'],
                                status: item['status'],
                                number_of_similar: item['number_of_similar'],

                                address: item['address'],
                                cause_of_change: item['cause_of_change'],
                                exchange: item['exchange'],
                                formula_of_exchange: item['formula_of_exchange'],
                                state: item['state'],
                                source_of_information: item['source_of_information'],
                                bathroom_type: item['bathroom_type'],
                                wall_material: item['wall_material'],
                                phone_line: item['phone_line'],
                                having_a_bath: item['having_a_bath'],
                                number_of_balconies: item['number_of_balconies'],
                                notes: item['notes'],
                                display_info: item['display_info'],
                            });
                        }
                    }
                }
            }
            this._ads.next(Object.assign({}, this.dataStore).AdList);
        }, error => console.log('Could not load ads.'));
    }

    load(id: number | string) {
        this.http.post(`${this.prefix_Url}/getAdById`,{id: id}).subscribe(data => {
            let notFound = true;

            this.dataStore.AdList.forEach((item, index) => {
                if (item.id === data['id']) {
                    this.dataStore.AdList[index] = {
                        id: data['id'],
                        link: data['link'],
                        updated_at: new Date(data['updated_at']),
                        ad_from: data['ad_from'],
                        type_of_structure: data['type_of_structure'],
                        type_of_apartment: data['type_of_apartment'],
                        number_of_rooms: data['number_of_rooms'],
                        floor: data['floor'],
                        storey_house: data['storey_house'],
                        total_area: data['total_area'],
                        living_space: data['living_space'],
                        kitchen_area: data['kitchen_area'],
                        price: data['price'],
                        telephones: data['telephones'],
                        number_of_photos: data['number_of_photos'],
                        status: data['status'],
                        number_of_similar: data['number_of_similar'],

                        address: data['address'],
                        cause_of_change: data['cause_of_change'],
                        exchange: data['exchange'],
                        formula_of_exchange: data['formula_of_exchange'],
                        state: data['state'],
                        source_of_information: data['source_of_information'],
                        bathroom_type: data['bathroom_type'],
                        wall_material: data['wall_material'],
                        phone_line: data['phone_line'],
                        having_a_bath: data['having_a_bath'],
                        number_of_balconies: data['number_of_balconies'],
                        notes: data['notes'],
                        display_info: data['display_info'],
                    };
                    notFound = false;
                }
            });

            if (notFound) {
                this.dataStore.AdList.push({
                    id: data['element']['id'],
                    link: data['element']['link'],
                    updated_at: new Date(data['element']['updated_at']),
                    ad_from: data['element']['ad_from'],
                    type_of_structure: data['element']['type_of_structure'],
                    type_of_apartment: data['element']['type_of_apartment'],
                    number_of_rooms: data['element']['number_of_rooms'],
                    floor: data['element']['floor'],
                    storey_house: data['element']['storey_house'],
                    total_area: data['element']['total_area'],
                    living_space: data['element']['living_space'],
                    kitchen_area: data['element']['kitchen_area'],
                    price: data['element']['price'],
                    telephones: data['element']['telephones'],
                    number_of_photos: data['element']['number_of_photos'],
                    status: data['element']['status'],
                    number_of_similar: data['element']['number_of_similar'],

                    address: data['element']['address'],
                    cause_of_change: data['element']['cause_of_change'],
                    exchange: data['element']['exchange'],
                    formula_of_exchange: data['element']['formula_of_exchange'],
                    state: data['element']['state'],
                    source_of_information: data['element']['source_of_information'],
                    bathroom_type: data['element']['bathroom_type'],
                    wall_material: data['element']['wall_material'],
                    phone_line: data['element']['phone_line'],
                    having_a_bath: data['element']['having_a_bath'],
                    number_of_balconies: data['element']['number_of_balconies'],
                    notes: data['element']['notes'],
                    display_info: data['element']['display_info'],
                });
                console.log(this.dataStore.AdList.length);
            }

            this._ads.next(Object.assign({}, this.dataStore).AdList);
        }, error => console.log('Could not load ad.'));
    }

    create(ad: Ad) {
        this.http.post(`${this.prefix_Url}/AddNewAd`, JSON.stringify(ad)).subscribe(data => {
            this.dataStore.AdList.push({
                id: data['id'],
                link: data['link'],
                updated_at: new Date(data['updated_at']),
                ad_from: data['ad_from'],
                type_of_structure: data['type_of_structure'],
                type_of_apartment: data['type_of_apartment'],
                number_of_rooms: data['number_of_rooms'],
                floor: data['floor'],
                storey_house: data['storey_house'],
                total_area: data['total_area'],
                living_space: data['living_space'],
                kitchen_area: data['kitchen_area'],
                price: data['price'],
                telephones: data['telephones'],
                number_of_photos: data['number_of_photos'],
                status: data['status'],
                number_of_similar: data['number_of_similar'],

                address: data['address'],
                cause_of_change: data['cause_of_change'],
                exchange: data['exchange'],
                formula_of_exchange: data['formula_of_exchange'],
                state: data['state'],
                source_of_information: data['source_of_information'],
                bathroom_type: data['bathroom_type'],
                wall_material: data['wall_material'],
                phone_line: data['phone_line'],
                having_a_bath: data['having_a_bath'],
                number_of_balconies: data['number_of_balconies'],
                notes: data['notes'],
                display_info: data['display_info'],
            });
            this._ads.next(Object.assign({}, this.dataStore).AdList);
        }, error => console.log('Could not create new ad.'));
    }

    update(ad: Ad) {
        this.http.request('put',`${this.prefix_Url}/updateAd`, {body:{newAd: JSON.stringify(ad)}})
            .subscribe(data => {
                this.dataStore.AdList.forEach((t, i) => {
                    if (t.id === data['id']) { this.dataStore.AdList[i] ={
                        id: data['id'],
                        link: data['link'],
                        updated_at: new Date(data['updated_at']),
                        ad_from: data['ad_from'],
                        type_of_structure: data['type_of_structure'],
                        type_of_apartment: data['type_of_apartment'],
                        number_of_rooms: data['number_of_rooms'],
                        floor: data['floor'],
                        storey_house: data['storey_house'],
                        total_area: data['total_area'],
                        living_space: data['living_space'],
                        kitchen_area: data['kitchen_area'],
                        price: data['price'],
                        telephones: data['telephones'],
                        number_of_photos: data['number_of_photos'],
                        status: data['status'],
                        number_of_similar: data['number_of_similar'],

                        address: data['address'],
                        cause_of_change: data['cause_of_change'],
                        exchange: data['exchange'],
                        formula_of_exchange: data['formula_of_exchange'],
                        state: data['state'],
                        source_of_information: data['source_of_information'],
                        bathroom_type: data['bathroom_type'],
                        wall_material: data['wall_material'],
                        phone_line: data['phone_line'],
                        having_a_bath: data['having_a_bath'],
                        number_of_balconies: data['number_of_balconies'],
                        notes: data['notes'],
                        display_info: data['display_info'],
                    }; }
                });

                this._ads.next(Object.assign({}, this.dataStore).AdList);
            }, error => console.log(`Could not update ${ad.id} ad.`));
    }

    remove(AdId: number) {
        this.http.request('delete',`${this.prefix_Url}/deleteAd`, {body:{id: AdId}})
        .subscribe(response => {
            this.dataStore.AdList.forEach((t, i) => {
                if (t.id === AdId) { this.dataStore.AdList.splice(i, 1); }
            });

            this._ads.next(Object.assign({}, this.dataStore).AdList);
        }, error => console.log(`Could not delete ${AdId} ad.`));
    }

}
