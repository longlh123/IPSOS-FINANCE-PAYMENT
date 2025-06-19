<h3>Project API</h3>

<blockquote>
</br>
<p><b>The API is used to login</b></p>

<pre>
    <code>
        axios.post('http://127.0.0.1:8000/api/login', {
            'email' : '{email}',
            'password' : '{password}'
        })
    </code>
</pre>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to logout</b></p>

<pre>
    <code>
        axios.post('http://127.0.0.1:8000/api/logout', {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
    <li><b>Show-Only-Enabled</b>: with 1, only show enabled projects, with 0, show all projects</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to show the list of departments</b></p>

<pre>
    <code>
        axios.get('http://127.0.0.1:8000/api/project-management/departments')
    </code>
</pre>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to show the list of project types</b></p>

<pre>
    <code>
        axios.get('http://127.0.0.1:8000/api/project-management/project-types')
    </code>
</pre>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to show the list of emails for assigning users to the project</b></p>

<pre>
    <code>
        axios.get('http://127.0.0.1:8000/api/users', {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to show all projects</b></p>

<pre>
    <code>
        axios.get('http://127.0.0.1:8000/api/project-management/projects', {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}',
                'Show-Only-Enabled': 1
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
    <li><b>Show-Only-Enabled</b>: with 1, only show enabled projects, with 0, show all projects</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to show a specific project</b></p>

<pre>
    <code>
        axios.get('http://127.0.0.1:8000/api/project-management/projects/{project_id}/show', {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}',
                'Show-Only-Enabled': 1
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
    <li><b>Show-Only-Enabled</b>: with 1, only show enabled projects, with 0, show all projects</li>
    <li><b>{project_id}</b>: set the ID of project to {project_id}</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to change the status of the project</b></p>

<pre>
    <code>
        axios.put('http://127.0.0.1:8000/api/project-management/projects/{project_id}/status',{
            "status": "{status-value}"
        },{
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{project_id}</b>: set the ID of project to {project_id}</li>
    <li><b>{status-value}</b>: choose one of the values planned, in coming, on going, completed, on hold, cancelled</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to disable the project</b></p>

<pre>
    <code>
        axios.put('http://127.0.0.1:8000/api/project-management/projects/{project_id}/disabled',{
            "disabled": {0 or 1}
        },{
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{project_id}</b>: set the ID of project to {project_id}</li>
    <li><b>{0 or 1}</b>: with 1, the project is disabled, with 0, the project is enabled.</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to create a new project</b></p>

<pre>
    <code>
        axios.post('http://127.0.0.1:8000/api/project-management/projects',{
            "internal_code" : "2024-212",
            "project_name" : "BEANBAG 2029",
            "platform" : "ifield",
            "planned_field_start" : "2024-06-23",
            "planned_field_end" : "2024-07-23",
            "project_types" : ["F2F"], //project type => tạm thời chỉ cho chọn 1 loại hình dự án nha
            "teams" : ["CEX"] //team CS
        },{
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to update the information of the project</b></p>

<pre>
    <code>
        axios.put('http://127.0.0.1:8000/api/project-management/projects/{project_id}/update',{
            "internal_code": "2024-212",
            "project_name": "BEANBAG 2030-test 2",
            "symphony": null,
            "job_number": null,
            "planned_field_start": "2024-06-23 00:00:00",
            "planned_field_end": "2024-07-23 00:00:00",
            "project_types": ["F2F"],
            "teams": ["CEX"],
            "permissions" : [
                "long.pham@ipsos.com",
                "Kenyatta.Pacocha@ipsos.com",
                "Malvina.Hessel@ipsos.com"
            ],
            "provinces": {
                "1" : {
                    "sample_size_main" : 100,
                    "price_main" : 100000,
                    "sample_size_booters" : 20,
                    "price_boosters" : 125000
                },
                "2" : {
                    "sample_size_main" : 100,
                    "price_main" : 120000,
                    "sample_size_booters" : 10,
                    "price_boosters" : 150000
                },
                "3" : {
                    "sample_size_main" : 200,
                    "price_main" : 120000,
                    "sample_size_booters" : 20,
                    "price_boosters" : 150000
                }
            }
        },{
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{project_id}</b>: set the ID of project to {project_id}</li>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to show employees</b></p>

<pre>
    <code>
        axios.get('http://127.0.0.1:8000/api/employee-management/employees', {
            headers: {
                'Content-Type': 'application/json'
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
</ol>
</br>
</blockquote>

<blockquote>
</br>
<p><b>The API is used to remove a specific province from the project</b></p>

<pre>
    <code>
        axios.delete('http://127.0.0.1:8000/api/project-management/projects/{project_id}/provinces/{province_id}/remove', {
            headers: {
                'Content-Type': 'application/json'
                'Authorization': 'Bearer {your-auth-token}'
            }
        })
    </code>
</pre>

<p>Comment:</p>
<ol>
    <li><b>{project_id}</b>: set the ID of project to {project_id}</li>
    <li><b>{province_id}</b>: set the ID of province to remove from the project</li>
    <li><b>{your-auth-token}</b>: set the authentication token of the currently logged-in user.</li>
</ol>
</br>
</blockquote>