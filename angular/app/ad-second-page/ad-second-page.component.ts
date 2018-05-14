import { Component, OnInit } from '@angular/core';
import {EventRouteService} from '../event.service';
import {ActivatedRoute} from '@angular/router';
import {Observable} from 'rxjs/Observable';
import {AdDataMenedgerService} from '../update-ad/ad-data-menedger.service';
import {Ad} from '../main-content/cities.service';
import {map} from 'rxjs/operators';
import {FormBuilder, FormControl, FormGroup, Validators} from '@angular/forms';

import * as _moment from 'moment';

@Component({
  selector: 'app-second-page',
  templateUrl: './ad-second-page.component.html',
  styleUrls: ['./ad-second-page.component.scss'],
})
export class AdSecondPageComponent implements OnInit {
    private id: number;
    public ad$: Observable<Ad>;
    protected ad: Ad;

    protected Page2: FormGroup;

    protected link: any;
    protected Adfrom: any;
    protected type_of_structure: any;
    protected type_of_apartment: any;
    protected number_of_rooms: any;

    constructor(private url: EventRouteService,
                private activateRoute: ActivatedRoute,
                private AdService: AdDataMenedgerService,
                protected fb: FormBuilder) {
        this.Page2 = fb.group({
            link: new FormControl('',[
                Validators.required,
                Validators.pattern("(https?:\\/\\/(?:www\\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\\.[^\\s]{2,}|www\\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\\.[^\\s]{2,}|https?:\\/\\/(?:www\\.|(?!www))[a-zA-Z0-9]\\.[^\\s]{2,}|www\\.[a-zA-Z0-9]\\.[^\\s]{2,})"),
                Validators.maxLength(1500),
                Validators.minLength(20)
            ]),
            ad_from: new FormControl('',[
                Validators.required,
                Validators.pattern("^[A-Za-zА-ЯЁа-яё][a-zа-яё ,;-]{1,}$"),
                Validators.maxLength(100),
                Validators.minLength(5)
            ]),
            type_of_structure: new FormControl('',[
                Validators.required,
                Validators.max(10),
                Validators.min(1),
            ]),
              type_of_apartment: new FormControl('',[
                  Validators.required,
                  Validators.max(10),
                  Validators.min(1),
              ]),
               number_of_rooms: new FormControl('',[
                   Validators.required,
                   Validators.max(10),
                   Validators.min(1),
               ]),
        });

        this.link = this.Page2.get('link');
        this.Adfrom = this.Page2.get('ad_from');
        this.type_of_structure = this.Page2.get('type_of_structure');
        this.type_of_apartment = this.Page2.get('type_of_apartment');
        this.number_of_rooms = this.Page2.get('number_of_rooms');

    }
    date = new FormControl(new Date().toLocaleDateString());

  ngOnInit() {
      this.id = this.activateRoute.snapshot.params['id'];
      this.ad$ = this.AdService.Ads.pipe(
          map(ads => ads.find(item => item.id == this.activateRoute.snapshot.params['id']))
      );
      this.ad$.subscribe(el=>{this.ad = el;});
  }

    log(){
      console.log(this.date.value.toString());
    }

}
