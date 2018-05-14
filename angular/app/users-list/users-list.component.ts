import { Component, OnInit } from '@angular/core';
import {EventRouteService} from '../event.service';

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.scss']
})
export class UsersListComponent implements OnInit {

  constructor(private url: EventRouteService) {
      this.url.getSignal('/users-list');
  }

  ngOnInit() {
  }

}
