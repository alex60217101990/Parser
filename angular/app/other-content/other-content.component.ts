import {Component, OnDestroy, OnInit} from '@angular/core';
import {EventRouteService, EventStartProcess} from '../event.service';
import {NgProgress, NgProgressRef} from '@ngx-progressbar/core';
import {Observable} from 'rxjs/Observable';
import {ParseOperationService} from './parse-operation.service';
import { PusherService} from './pusher.service';
declare var Pusher: any;
@Component({
  selector: 'app-other-content',
  templateUrl: './other-content.component.html',
  styleUrls: ['./other-content.component.scss']
})
export class OtherContentComponent implements OnInit {
    longOperation$: Observable<boolean>;

    protected count_of_ad: string='0';

    protected select_region: string='od/';
    protected select_count: number = 10;
    protected RegionsList: Array<string> =
        [
            'Одесская','Днепропетровская','Черниговская',
            'Харьковская','Житомирская','Полтавская','Херсонская',
            'Киевская','Запорожская','Винницкая','Кировоградская',
            'Николаевская','Сумская','Львовская','Черкасская',
            'Хмельницкая','Волынская','Ровенская','Ивано-Франковская',
            'Тернопольская','Закарпатская','Черновицкая'
        ];

/*pusher test*/
    private pusher: any;
    protected $proxy: any;
    protected $pr_start_end: any;
  constructor(private url: EventRouteService,
              private loadService: ParseOperationService,
              protected $StartStop: EventStartProcess,
              /*protected pusher: PusherService*/) {
    this.url.getSignal('/other');
      //this.longOperation$ = this.loadService.parseStart;

      /* Server event new parse ad. */
      this.pusher = new Pusher('f23a9c9e8015e33d82ea', {
          cluster: 'eu',
          encrypted: true
      });
      this.pusher.logToConsole = true;

      let channel = this.pusher.subscribe('my-channel');
      channel.bind('my-event',  (data) => {
          //console.log('Count: '+data.message);
          this.count_of_ad = data.message;
      });
      /* Server event new parse ad. */
  }

  public index_of: number=0;

  public value = 0;
  public indicate: boolean = true;
  public proxy_list: Array<Proxy>=new Array<Proxy>();
    ngOnInit() {
        /* Server event new parse proxy. */
        this.$proxy = new Pusher('XXXXXXXXXXXXXXXXXXXXXXXX', {
            cluster: 'eu',
            encrypted: true
        });
        this.$proxy.logToConsole = true;

        let channel = this.$proxy.subscribe('proxy-channel');
        channel.bind('my-event-proxy',  (data) => {
            this.proxy_list.push({ ip: data.ip, proxy_type: data.proxy_type,
                bool_https: data.bool_https, port: data.port, color: data.res/*==='true'?'#1DE9B6':'#FF1744'*/ });
            this.value = Math.ceil(this.proxy_list.length/3);
        });
        /* Server event new parse proxy. */

        /* Server event new start/cancel parse. */
        this.$pr_start_end = new Pusher('XXXXXXXXXXXXXXXXXXXXXXXXX', {
            cluster: 'eu',
            encrypted: true
        });
        this.$pr_start_end.logToConsole = true;

        let channel3 = this.$pr_start_end.subscribe('proxy-channel-start');
        channel3.bind('my-event-proxy-start',  (data) => {
            this.indicate = data.indicate;
        });
        /* Server event new start/cancel parse. */
    }

    ngOnDestroy() {
    }

    ChangeRegion($element: string, index: number){

      this.index_of = index;

      switch ($element){
          case 'Одесская': {
              this.select_region = 'od/';
              break;
          }
          case 'Харьковская': {
              this.select_region = 'kha/';
              break;
          }
          case 'Днепропетровская': {
              this.select_region = 'dnp/';
              break;
          }
          case 'Черниговская': {
              this.select_region = 'chn/';
              break;
          }
          case 'Житомирская': {
              this.select_region = 'zht/';
              break;
          }
          case 'Полтавская': {
              this.select_region = 'pol/';
              break;
          }
          case 'Херсонская': {
              this.select_region = 'khe/';
              break;
          }
          case 'Киевская': {
              this.select_region = 'ko/';
              break;
          }
          case 'Запорожская': {
              this.select_region = 'zap/';
              break;
          }
          case 'Винницкая': {
              this.select_region = 'vin/';
              break;
          }
          case 'Кировоградская': {
              this.select_region = 'kir/';
              break;
          }
          case 'Николаевская': {
              this.select_region = 'nik/';
              break;
          }
          case 'Сумская': {
              this.select_region = 'sum/';
              break;
          }
          case 'Львовская': {
              this.select_region = 'lv/';
              break;
          }
          case 'Черкасская': {
              this.select_region = 'chk/';
              break;
          }
          case 'Хмельницкая': {
              this.select_region = 'khm/';
              break;
          }
          case 'Волынская': {
              this.select_region = 'vol/';
              break;
          }
          case 'Ровенская': {
              this.select_region = 'rov/';
              break;
          }
          case 'Ивано-Франковская': {
              this.select_region = 'if/';
              break;
          }
          case 'Тернопольская': {
              this.select_region = 'ter/';
              break;
          }
          case 'Закарпатская': {
              this.select_region = 'zak/';
              break;
          }
          case 'Черновицкая': {
              this.select_region = 'chv/';
              break;
          }
          default:{
              this.select_region = 'kha/';
              break;
          }
      }
    }

    ChangeCountPage($count: number){
      this.select_count = $count;
      this.value = 0;
      this.proxy_list.length = 0;
    }

    startProxyParse(){
        this.loadService.startProxyParse().subscribe(res=>{
            console.log(res);
        });
    }
}

export interface Proxy{
    ip: string;
    proxy_type: string;
    bool_https: string;
    port: boolean;
    color: string;
}