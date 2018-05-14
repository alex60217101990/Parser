import {EventEmitter, Injectable, Output} from '@angular/core';
import { HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs/Observable';
import 'rxjs/add/operator/map'
import {hasProperties} from 'codelyzer/util/astQuery';
import {Logout, Token} from './token';
import {Router} from '@angular/router';
import {EventAvatarLoad, EventLogoutService, EventTitleService} from './event.service';
import {NgxPermissionsService} from 'ngx-permissions';

@Injectable()
export class AuthenticationService {
    public token: string;
    public result: boolean;


    public message$: EventEmitter<string>;
 //   public eventOfLogout$: EventEmitter<boolean>;

    constructor(private http: HttpClient, private route: Router, private toogle: EventLogoutService,
                private title$: EventTitleService, private AvaLoad: EventAvatarLoad) {
        // set token if saved in local storage
        var currentUser = localStorage.getItem('id_token');//JSON.parse(localStorage.getItem('id_token'));
        this.token = currentUser; //&& currentUser.id_token;

        this.message$ = new EventEmitter();
    }
    public login(email: string, password: string, login: string): Observable<any>|any {
        return this.http.post<Token>('/auth/login', {email: email, login: login, password: password})
            .map((response: Token) => {
                // login successful if there's a jwt token in the response
                let token = response.token;
                if (!!token) {
                    // set token property
                    this.token = response.token; this.result = true;
                    // store username and jwt token in local storage to keep user logged in between page refreshes
                    localStorage.setItem('id_token', token);
                    localStorage.setItem('user_data', JSON.stringify({login: login}));

                    this.title$.getSignal(login);

                    this.toogle.getSignal(false);
                    return response;
                } else {
                    // return false to indicate failed login
                    return response;
                }
            });
    }

    public logout(): void {
        // clear token remove user from local storage to log user out
        this.token = null;
        this.http.get<Logout>('/auth/logout').subscribe((message: Logout)=> {
            console.log(message.message);
            if(message.message == 'Successfully logged out') {
                localStorage.removeItem('id_token');
                localStorage.removeItem('user_data');
                localStorage.removeItem('url_avatar');

                this.AvaLoad.getSignal('');
                sessionStorage.removeItem('avatar');

                console.log('Successfully logged out');
                this.route.navigate(['/login']);

                this.message$.emit('Successfully logged out.');
                this.toogle.getSignal(true);
                this.title$.getSignal('Welcome to Anvel!');
            }
            else{
                this.message$.emit('Error of logout.');
            }
        });
    }


    public getAvatar():Observable<string>{
        return this.http.post<Avatar>('/content/getUserAva',1)
            .map(response => {
            return response.photo;
        });
    }

    /**
     * Method for load all permissions on app.
     * @return {Observable<Array<string>>}
     */
    public getPermissions():Observable<Array<string>>{
        return this.http.post<Array<string>>('/content/loadPermissions',1).map((permissions) => {
            return permissions;
        });
    }

}

export interface Avatar{
    photo: string;
    info: string;
}
