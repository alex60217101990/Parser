import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import 'rxjs/add/operator/toPromise';
import {NgxPermissionsService} from 'ngx-permissions';

@Injectable()
export class AppLoadService {

  constructor(private http: HttpClient, private permissionsService: NgxPermissionsService) { }

    /**
     * Method for load all permissions on app.
     */
    initializeApp(): Promise<any> {
        return new Promise((resolve) => {
            const perm = ["ADMIN", "USER"];
            this.permissionsService.loadPermissions(perm);
            this.http.post<Array<string>>('/content/loadPermissions',1).subscribe(permissions => {
                this.permissionsService.loadPermissions(permissions);
                console.log(permissions);
                resolve();
            });
        });
    }
}
