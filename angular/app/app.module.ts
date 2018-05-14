import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { BrowserModule } from '@angular/platform-browser';
import {MatButtonModule, MatCheckboxModule, MatNativeDateModule, MatTableModule} from '@angular/material';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';
import { HttpClientModule }   from '@angular/common/http';
import {UIRouterModule} from "@uirouter/angular";
import {MatDialogModule} from '@angular/material/dialog';
import {AppComponent, DialogOverviewExampleDialog} from './app.component';
import {ReactiveFormsModule} from '@angular/forms';
import {MatCardModule} from '@angular/material/card';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { TokenInterceptor } from './token.interceptor';
import { MalihuScrollbarModule } from 'ngx-malihu-scrollbar';
import {MatSnackBarModule} from '@angular/material/snack-bar';
import {MatIconModule, MatIconRegistry} from '@angular/material';


/*Angular material - forms.*/
import {MatInputModule} from '@angular/material/input';
import { AuthPageControllerComponent } from './auth-page-controller/auth-page-controller.component';
import {
    AuthPageRegisterControllerComponent
} from './auth-page-register-controller/auth-page-register-controller.component';
import {MatToolbarModule} from '@angular/material/toolbar';
import {MatTabsModule} from '@angular/material/tabs';
import { AuthRoutesComponent } from './auth-routes/auth-routes.component';
import { MainContentComponent } from './main-content/main-content.component';
import {OtherContentComponent} from './other-content/other-content.component';
import {RouterModule, Routes} from '@angular/router';
import {AuthService} from './auth.service';
import {AuthGuard} from './auth.guard';
import {AuthenticationService} from './authentication.service';
import {RegisterService} from './register.service';
import {JwtInterceptor} from './jwt.interceptor';

import {MatExpansionModule} from '@angular/material/expansion';

import {
    EventAdService, EventAvatarLoad, EventAvatarReRender, EventFormService,
    EventLogoutService, EventRouteService, EventService, EventStartProcess,
    EventTitleService
} from './event.service';
import {RECAPTCHA_SETTINGS, RecaptchaSettings, RecaptchaModule } from 'ng-recaptcha';
import {CitiesService} from './main-content/cities.service';
import { UsersListComponent } from './users-list/users-list.component';
import {MatMenuModule} from '@angular/material/menu';
import {MatStepperModule} from '@angular/material/stepper';
import {MatSortModule} from '@angular/material/sort';
import {MatPaginatorModule} from '@angular/material/paginator';
import {AdComponent, DialogAvatar} from './ad/ad.component';

import 'hammerjs';
// app.module.ts
import { NgxGalleryModule } from 'ngx-gallery';
// Import your library
import {NgxPermissionsGuard, NgxPermissionsModule, NgxPermissionsService} from 'ngx-permissions';
import { APP_INITIALIZER } from '@angular/core';
import {AppLoadService} from './app-load-service.service';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatSelectModule} from '@angular/material';
import { UpdateAdComponent } from './update-ad/update-ad.component';
import {MatRadioModule} from '@angular/material/radio';
//Query router progress bar.
import { NgProgressModule } from '@ngx-progressbar/core';
import { NgProgressRouterModule } from '@ngx-progressbar/router';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {ParseOperationService} from './other-content/parse-operation.service';
import {AdDataMenedgerService} from './update-ad/ad-data-menedger.service';
import { AdSecondPageComponent } from './ad-second-page/ad-second-page.component';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';

//datapicker
import {MatDatepickerModule} from '@angular/material/datepicker';
//google map
import {AgmCoreModule, GoogleMapsAPIWrapper, MapsAPILoader} from '@agm/core';
import { GoogleMapComponent } from './google-map/google-map.component';
import {GoogleMapService} from './google-map/google-map.service';
import {PusherService} from './other-content/pusher.service';
// Import ng-circle-progress
import { NgCircleProgressModule } from 'ng-circle-progress';


// определение маршрутов
const appRoutes: Routes =[
    { path: '', component: MainContentComponent, pathMatch:'full', canActivate: [AuthGuard]},
    { path: 'login', component: AuthRoutesComponent, pathMatch:'full'},
    { path: 'other', component: OtherContentComponent, pathMatch:'full', canActivate: [AuthGuard]},
    { path: 'users-list', component: UsersListComponent, pathMatch:'full',
        canActivate: [AuthGuard, NgxPermissionsGuard],
        data: {
            permissions: {
                only: 'Admin panel',
                redirectTo: ''
            }
        }
    },
    {path: 'ad/:id', component: UpdateAdComponent, pathMatch:'full', canActivate: [AuthGuard]},
    { path: '**', redirectTo: '/'}
];

export function init_app(appLoadService: AppLoadService) {
    return () => appLoadService.initializeApp();
}


@NgModule({
  declarations: [
    AppComponent,
    AuthPageControllerComponent,
    AuthPageRegisterControllerComponent,
    AuthRoutesComponent,
      OtherContentComponent,
      MainContentComponent,
      DialogOverviewExampleDialog,
      AdComponent,
      UsersListComponent,
      UpdateAdComponent,
      AdSecondPageComponent,
      DialogAvatar,
      AdSecondPageComponent,
      GoogleMapComponent,
  ],
  imports: [
    BrowserAnimationsModule,
    BrowserModule,
    FormsModule,
    HttpModule,
    MatButtonModule,
    MatCheckboxModule,
      ReactiveFormsModule,
      MatCardModule,
      MatDialogModule,
      MatTableModule,
      MatInputModule,
      MatSnackBarModule,
      MatIconModule,
      RecaptchaModule.forRoot(),
      MatFormFieldModule,
      MatSelectModule,
      MatRadioModule,
      MatProgressBarModule,

      MatExpansionModule,
      MatMenuModule,
      MatStepperModule,
      MatSortModule,
      MatPaginatorModule,
      NgxGalleryModule,
      // Specify your library as an import
      NgxPermissionsModule.forRoot(),
      //query progress bar
      NgProgressModule.forRoot(),
      NgProgressRouterModule,
      MatProgressSpinnerModule,
      MatDatepickerModule,
      MatNativeDateModule,
      AgmCoreModule.forRoot({
          apiKey: 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
          libraries: ["places"]
      }),

      NgCircleProgressModule.forRoot({
          "radius": 100,
          "outerStrokeWidth": 10,
          "innerStrokeWidth": 5,
          "showBackground": false
      }),


      MatToolbarModule,
      MatTabsModule,
      HttpClientModule,
      MalihuScrollbarModule.forRoot(),
      RouterModule.forRoot(appRoutes),
  ],
  exports: [
    MatButtonModule,
    MatCheckboxModule,
  ],
  providers: [
      {
          provide: RECAPTCHA_SETTINGS,
          useValue: { siteKey: 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' } as RecaptchaSettings,
      },
      {
          provide: HTTP_INTERCEPTORS,
          useClass: JwtInterceptor,
          multi: true
      },
      {
          provide: HTTP_INTERCEPTORS,
          useClass: TokenInterceptor,
          multi: true
      },
      AuthService,
      AuthGuard,
      AuthenticationService,
      RegisterService,
      MatIconRegistry,
      EventService,
      EventLogoutService,
      CitiesService,
      EventRouteService,
      EventTitleService,
      EventFormService,
      EventAdService,
      EventAvatarLoad,
      ParseOperationService,
      EventStartProcess,
      AdDataMenedgerService,
      GoogleMapService,
      PusherService,


      EventAvatarReRender,

      /*DictionaryService,*/
     /* AppLoadService,
      { provide: APP_INITIALIZER, useFactory: init_app, deps: [AppLoadService], multi: true },*/

  ],
    entryComponents: [AppComponent, DialogOverviewExampleDialog, DialogAvatar],
  bootstrap: [AppComponent],
})
export class AppModule { }
