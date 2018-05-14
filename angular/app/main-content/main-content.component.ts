import {AfterViewInit, Component, OnDestroy, OnInit, ViewChild} from '@angular/core';
import {CitiesService, Ad} from './cities.service';
import {EventAdService, EventRouteService} from '../event.service';
import {MatTableDataSource, MatSort, MatPaginator} from '@angular/material';
import {MalihuScrollbarService} from 'ngx-malihu-scrollbar/dist/lib';
import {Router} from '@angular/router';
import { ChangeDetectorRef } from '@angular/core';
declare var jquery:any;
declare var $ :any;
import {FormBuilder, FormGroup} from '@angular/forms';
import {AdDataMenedgerService} from '../update-ad/ad-data-menedger.service';
import {Observable} from 'rxjs/Observable';

@Component({
  selector: 'app-main-content',
  templateUrl: './main-content.component.html',
  styleUrls: ['./main-content.component.scss']
})
export class MainContentComponent implements OnInit, OnDestroy, AfterViewInit {
  public absent: boolean =true;
  public previous: boolean;
  public redacted: boolean=false;
/*  public user_absent_block:boolean = true;
  public is_present_block: boolean = false;*/
  public Ads: Array<Ad>=new Array<Ad>();
  public IndColor: string = "";

  public options: FormGroup;


    displayedColumns = ['id', 'link', 'updated_at', 'ad_from', 'type_of_structure', 'type_of_apartment', 'number_of_rooms',
        'floor', 'storey_house', 'total_area', 'living_space', 'kitchen_area',
        'price', 'telephones', 'number_of_photos', 'status', 'number_of_similar',
        'address','cause_of_change', 'exchange', 'formula_of_exchange',
     'state', 'source_of_information', 'bathroom_type', 'wall_material', 'phone_line', 'having_a_bath', 'number_of_balconies',
     'notes', 'display_info'];


        // dataSource;
    dataSource = new MatTableDataSource(/*this.Ads*/);

  constructor(private data: CitiesService, private url: EventRouteService, private router: Router,
              private adRoute: EventAdService, private scrollbarService: MalihuScrollbarService, fb: FormBuilder){
      this.url.getSignal('/');
      this.options = fb.group({
          filterText: '',
          selectElement: 'id',
      });
  }
   public getFontSize():number{ return 22; }
  ngOnInit() {
      (this.Ads.length>0)?this.absent=false:this.absent=true;

      this.data.getCities().subscribe((info)=>{
         this.Ads = info;
          this.dataSource.data = info;
      });

      /* configure filter */
      this.dataSource.filterPredicate =
          (data: Ad, filter: string) => {
          switch (this.options.value.selectElement){
              case 'id':
                  return (data.id == parseInt(filter, 10));
              case 'link':
                  return data.link.indexOf(filter) != -1;
              case 'updated at':{
                  let date = new Date(filter);
                  return ((+date == +data.updated_at)||(date.getTime() == data.updated_at.getTime())||
                      (date.valueOf() == data.updated_at.valueOf())||(Number(date) == Number(data.updated_at))||
                      (date.getFullYear() == data.updated_at.getFullYear() && date.getDate() == data.updated_at.getDate() &&
                      date.getMonth() == data.updated_at.getMonth()));
              }
              case 'ad from': {
                  console.log(filter);
                  return data.ad_from.toLocaleLowerCase().trim().indexOf(filter) != -1;
              }
              case 'type of structure':
                  return data.type_of_structure.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'type of apartment':
                  return data.type_of_apartment.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'number of rooms':
                  return (data.number_of_rooms == parseInt(filter, 10));
              case 'floor':
                  return (data.floor == parseInt(filter, 10));
              case 'storey house':
                  return (data.storey_house == parseInt(filter, 10));
              case 'total area':
                  return (data.total_area == parseFloat(filter));
              case 'living space':
                  return (data.living_space == parseFloat(filter));
              case 'kitchen area':
                  return (data.kitchen_area == parseFloat(filter));
              case 'price':
                  return (data.price == parseFloat(filter));
              case 'telephones':
                  return data.telephones.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'number of photos':
                  return (data.number_of_photos == parseInt(filter, 10));
              case 'status':
                  return data.status.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'number of similar':
                  return (data.number_of_similar == parseInt(filter, 10));
              case 'address':
                  return data.address.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'cause of change':
                  return data.cause_of_change.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'exchange':
                  return filter=='1'?Boolean(data.exchange):!Boolean(data.exchange);
              case 'formula of exchange':
                  return data.formula_of_exchange.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'state':
                  return data.state.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'source of information':
                  return data.source_of_information.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'bathroom type':
                  return data.bathroom_type.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'wall material':
                  return data.wall_material.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'phone line':
                  return filter=='1'?Boolean(data.phone_line):!Boolean(data.phone_line);
              case 'having a bath':
                  return filter=='1'?Boolean(data.having_a_bath):!Boolean(data.having_a_bath);
              case 'number of balconies':
                  return (data.number_of_balconies == parseInt(filter, 10));
              case 'notes':
                  return data.notes.toLocaleLowerCase().trim().indexOf(filter) != -1;
              case 'display info':
                  return data.display_info.toLocaleLowerCase().trim().indexOf(filter) != -1;
          }
      };
  }


    ngOnDestroy() {
        //this.Ads.length = 0;
        //this.data.ngOnDestroy();
        console.log('Service destroy');
    }

    @ViewChild(MatSort) sort: MatSort;
    @ViewChild(MatPaginator) paginator: MatPaginator;

    ngAfterViewInit() {
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;

        this.scrollbarService.initScrollbar('#tableScroll', { axis: 'yx', theme: 'minimal-dark', scrollButtons: { enable: true },
            setTop: '0px',setLeft: '30px', autoHideScrollbar: true });
    }


    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    searchStyle():void {
        this.IndColor = 'primary';
    }
    searchStyle1():void {
        this.IndColor = '';
    }

    selectedRowIndex: number = -1;
    highlight(row){
        this.selectedRowIndex = row.id;
        this.adRoute.getSignal(row);
        this.router.navigate(['/ad', row.id]);
    }
    disconnect(): void {}
}

