import {Component, OnDestroy, OnInit} from '@angular/core';
import {EventRouteService} from '../event.service';
import {Observable} from 'rxjs/Observable';
import {Ad} from '../main-content/cities.service';
import {ActivatedRoute} from '@angular/router';
//import {map} from 'rxjs/operator/map';
import { map } from 'rxjs/operators';
import { of } from 'rxjs/observable/of';
import { _throw } from 'rxjs/observable/throw';
import { delayWhen } from 'rxjs/operators';
import { timer } from 'rxjs/observable/timer';
import {AdDataMenedgerService} from './ad-data-menedger.service';

@Component({
  selector: 'app-update-ad',
  templateUrl: './update-ad.component.html',
  styleUrls: ['./update-ad.component.scss']
})
export class UpdateAdComponent implements OnInit, OnDestroy {
private id: number;
public ad$: Observable<Ad>;
  constructor(private url: EventRouteService,
              private activateRoute: ActivatedRoute,
              private AdService: AdDataMenedgerService) {
      this.url.getSignal('/');
  }

  ngOnInit() {
      this.id = (this.activateRoute.snapshot.params['id']);
      this.AdService.Ads.subscribe(el=>{
        if(el.length>0) return;
        else this.AdService.load(this.id);
      });
  }

  ngOnDestroy(){

  }

}
