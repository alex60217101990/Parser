import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {EventFormService} from '../event.service';

@Component({
  selector: 'app-auth-routes',
  templateUrl: './auth-routes.component.html',
  styleUrls: ['./auth-routes.component.scss']
})
export class AuthRoutesComponent implements OnInit {

    public firstFormGroup: boolean = true;
    public secondFormGroup: boolean = false;
  constructor(private $form: EventFormService) {
      this.$form.form$.subscribe(type => {
          if(type === 'login'){
              this.firstFormGroup = true;
              this.secondFormGroup = false;
          }
          if(type === 'register'){
              this.firstFormGroup = false;
              this.secondFormGroup = true;
          }
      });
  }

    ngOnInit(): void/*Observable<any[]>*/ {

    }

}
