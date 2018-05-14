import {
    AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef,
    Component, ElementRef, HostListener, Inject, NgZone, OnDestroy, OnInit,
    ViewChild
} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {UIRouterModule} from "@uirouter/angular";
import {MatSnackBar, MatSort} from '@angular/material';
declare var jquery:any;
declare var $ :any;
import {MatIconRegistry} from '@angular/material';
import 'rxjs/add/operator/map';
import {MalihuScrollbarService} from 'ngx-malihu-scrollbar/dist/lib';
import {AuthenticationService} from './authentication.service';
import {
    EventAvatarLoad, EventAvatarReRender, EventLogoutService, EventRouteService, EventService, EventStartProcess,
    EventTitleService
} from './event.service';
import * as Rx from "rxjs/Rx";


import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import {AuthService} from './auth.service';
import {ActivatedRoute, NavigationEnd, NavigationStart, Router, RouterEvent, UrlSegment} from '@angular/router';
import {Observable} from 'rxjs/Observable';
import 'rxjs/add/operator/filter';
import 'rxjs/add/operator/timeInterval';
import {DomSanitizer} from '@angular/platform-browser';
import { NgxPermissionsService } from 'ngx-permissions';
import {ParseOperationService} from './other-content/parse-operation.service';
import {toSubscriber} from 'rxjs/util/toSubscriber';
import {NgProgress} from '@ngx-progressbar/core';


interface ActivePage{
    url: string;
    indicate: boolean;
}

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements AfterViewInit, OnDestroy, OnInit {
    title: string = 'Welcome to Anvel!';
    public id_of_route: number = 0;
    private end_navigation: string = '';

    public toogleClass: Array<ActivePage> = new Array<ActivePage>();

    activation: boolean;
    className: Array<string>;
    public scrollbarOptions = {axis: 'yx', theme: 'minimal-dark'};

    /**
     * Start process.
     */
    protected longOperation$: Observable<boolean>;


    constructor(private http: HttpClient, private scrollbarService: MalihuScrollbarService,
                private authService: AuthenticationService, public snackBar: MatSnackBar,
                private provider: EventService, public dialog: MatDialog, private auth: AuthService,
                private toogle$: EventLogoutService, private router: Router,
                private routeEvent$: EventRouteService, private title$: EventTitleService,
                iconRegistry: MatIconRegistry, sanitizer: DomSanitizer,
                iconRegistry1: MatIconRegistry, private AvaLoad: EventAvatarLoad,
                private permissionsService: NgxPermissionsService, private $StartStop: EventStartProcess,
                private $renderAva: EventAvatarReRender, private loadService: ParseOperationService) {
        this.className = new Array<string>();
        this.className.push("md-dialog-container");
        authService.message$.subscribe(message => this.onGetMessage(message));
        provider.result$.subscribe(message => {/*this.onGetMessage(message);*/
            this.openDialog(message);
            this.activation = true;
        });

        (auth.isAuthenticated() !== true) ? this.activation = true : this.activation = false;
        toogle$.true$.subscribe(toogle$ => {
            this.activation = toogle$;

            // перезвгрузка разрешений после регистрации или выхода из системы.
            if (toogle$ == true) {
                const perm = [""];
                this.permissionsService.loadPermissions(perm);
            } else {
                this.authService.getPermissions().subscribe(permissions => {
                    this.permissionsService.loadPermissions(permissions);
                    console.log(permissions);
                });
            }
        });


        // Подписываемся на события изменеия маршрута.
        router.events.filter(e => e instanceof RouterEvent).subscribe(e => {
            //console.log(e['id'], e['url']);
            this.id_of_route = e['url'];
        });
        router.events.filter(e => e instanceof NavigationEnd).subscribe(e => {
            this.end_navigation = e['urlAfterRedirects'];
        });

        routeEvent$.path$.subscribe(url => this.isActiveMethod(url));
        this.toogleClass.push({url: '/', indicate: true});
        this.toogleClass.push({url: '/other', indicate: true});
        this.toogleClass.push({url: '/users-list', indicate: true});


        //регистрируем SVG изображение.
        iconRegistry.addSvgIcon(
            'home-passive',
            sanitizer.bypassSecurityTrustResourceUrl('../img/home.svg'));
        iconRegistry1.addSvgIcon(
            'home-active',
            sanitizer.bypassSecurityTrustResourceUrl('../img/home-active.svg'));

        //получение текущего маршрута компонента.
        router.events
            .filter(event => event instanceof NavigationEnd)
            .subscribe((event: NavigationEnd) => {
                // You only receive NavigationStart events
                // console.log(event.urlAfterRedirects);
                if (event.urlAfterRedirects === '/login')
                    this.isActiveMethod('/login');
            });


        //permissions load.
        const perm = ["ADMIN", "USER"];
        this.permissionsService.loadPermissions(perm);
        this.authService.getPermissions().subscribe(permissions => {
            this.permissionsService.loadPermissions(permissions);
            console.log(permissions);
        });


        // re-rendering avatar url.
        this.$renderAva.render_event$.subscribe($event => {
            this.avatar = $event;
            /*     Rx.Observable.interval(1000)
                     .takeWhile(() => !stopCondition)
                     .subscribe(i => {
                         // This will be called every 10 seconds until `stopCondition` flag is set to true
                         console.log(this.avatar);
                         (counter === 1)?stopCondition=true:stopCondition=false;
                         this.avatar = bufer;
                         counter++;
                     })*/
        });

        /*event*/
        this.$StartStop.start_event$.subscribe(indicate => {
            if (indicate.$event == true) {
                this.visibleLoader = !this.visibleLoader;
                this.loadService.parserStartAsync(indicate).subscribe(rez => {
                    console.log(rez);
                    this.visibleLoader = !this.visibleLoader;
                });
            }
            else
                this.visibleLoader = true;
        });

    };

    visibleLoader: boolean = true;


    public avatar: string = '';

    /**
     * Method for update button class.
     * @return void
     * @param {string} path
     */
    private isActiveMethod(path: string): void {
        for (let p of this.toogleClass) {
            if (p.url === path) {
                p.indicate = false;
            } else {
                p.indicate = true;
            }
        }
    }


    /*----------------------------------------------------------------------------------------------*/
    openDialog(message: string): void {
        let dialogRef = this.dialog.open(DialogOverviewExampleDialog, {
            width: '50%',
            // panelClass: this.className,
            data: {title: 'Error message.', text: message}
        });

        dialogRef.afterClosed().subscribe(result => {
            //
        });
    }

    /*-----------------------------------------------------------------------------------------------*/

    resolved(captchaResponse: string) {
        console.log(`Resolved captcha with response ${captchaResponse}:`);
    }


    /**
     * Обработчик события сервиса, срабатывает на выход из системы.
     * @return void
     * @param {string} text
     */
    private onGetMessage(text: string): void {
        // do something with added item
        this.ShowLogoutResultMessage(text, 'exit');
    }

    /**
     * Material snack bar info message.
     * @param string, string
     * @return void
     */
    private ShowLogoutResultMessage(message: string, action: string) {
        this.snackBar.open(message, action, {
            duration: 5000,
            extraClasses: this.className
        });
    }


    /**
     * Method route to "Other" component content.
     * @param void
     * @return void
     */
    public OtherRouterLink(): void {
        this.router.navigate(['other']);
    }

    /**
     * Method route to "Users List" component content.
     * @param void
     * @return void
     */
    public UsersListRouterLink(): void {
        this.router.navigate(['users-list']);
    }

    /**
     * Method route to "Main page" component content.
     * @param void
     * @return void
     */
    public MainPageLink(): void {
        this.router.navigate(['']);
    }


    ngOnInit(): void {
        this.title$.title$.subscribe((text: string) => {
            this.title = text;
        });
        if (localStorage.getItem('user_data') !== null)
            this.title = JSON.parse(localStorage.getItem('user_data')).login;

        this.Small = (window.innerWidth > 700) ? false : true;

        this.AvaLoad.url$.subscribe(url => {
            this.avatar = url;
        });

        //clear session storage.
        this.router.events
            .filter(event => event instanceof NavigationStart)
            .subscribe((event: NavigationStart) => {
                if (event.url === '/login')
                    sessionStorage.removeItem('avatar');
            });

        this.authService.getAvatar().subscribe(url => this.avatar = url);
    }

    /*----------------------------------------------------------------------------------------------------------------------------*/
    public Small: boolean = true;


    public onLogout(): boolean {
        this.authService.logout();
        this.avatar = '';
        return false;
    }

    ngAfterViewInit() {
        // this.scrollbarService.initScrollbar('#content', { axis: 'y', theme: 'dark', scrollButtons: { enable: true } });
        this.scrollbarService.initScrollbar(document.body, {
            axis: 'y', theme: 'minimal-dark', scrollButtons: {enable: false},
            setLeft: "15px", autoHideScrollbar: true
        });
    }

    /**
     * Indicator of Buttons color class
     * @type {boolean | boolean}
     */
    public topp: boolean = (window.pageXOffset > 600) ? true : false;
    public topp1: boolean = true;

    /**
     * "System's event's".
     * Event of window resize.
     * @param $event
     */
    @HostListener('window:resize', ['$event']) onResizeEvent($event) {
        //const horizontalOffset = window.pageXOffset||0;
        if ($event.target.innerWidth < 700) {
            ($event.target.innerWidth < 600) ? this.topp = false : this.topp = true;
            ($event.target.innerWidth > 600 && $event.target.innerWidth < 700) ? this.topp1 = true : this.topp1 = false;
            this.Small = true;
        } else {
            this.Small = false;
        }
    }

    public ngOnDestroy(): void {
        sessionStorage.removeItem('avatar');
    }

    /**
     * Method for router event progress bar.
     */
    options = {
        min: 8,
        max: 100,
        ease: 'linear',
        speed: 700,
        trickleSpeed: 800,
        meteor: true,
        direction: 'ltr+',
        color: '#F50057',
        spinner: false,
        thick: true
    };
}




@Component({
    selector: 'dialog-overview-example-dialog',
    templateUrl: 'dialog-overview-example-dialog.html',
    styleUrls: ['./app.component.scss']
})
export class DialogOverviewExampleDialog {

    constructor(public dialogRef: MatDialogRef<DialogOverviewExampleDialog>,
                @Inject(MAT_DIALOG_DATA) public data: any, private http: HttpClient) {
    }

    onNoClick(): void {
        this.dialogRef.close();
    }

    resolved(captchaResponse: string) {
        console.log(`Resolved captcha with response ${captchaResponse}:`);
    }


}