# event_api
<h3>Events Controller</h3>
<h4>Event Management system using Laravel framework</h4>
<table>
    <tr>
    <th>URI</th>
    <th>Method</th>
    <th>Function</th>
    <th>Purpose</th>
    </tr> 
    <tr>
        <td>/</td>
        <td>GET</td>
        <td>index</td>
        <td>Viewing your events, Admin can see all events</td>
    </tr>
    <tr>
        <td>/mems</td>
        <td>GET</td>
        <td>showMems</td>
        <td>Viewing the members of events you are invited to, Admin can see members of all events</td>
    </tr>
     <tr>
        <td>/register</td>
         <td>POST</td>
        <td>register</td>
        <td>Register yourself as an user</td>
    </tr>
     <tr>
        <td>/inv/{e_id}</td>
         <td>POST</td>
        <td>invite</td>
        <td>Invite another user to an event, cannot invite if already present</td>
    </tr>
     <tr>
        <td>/create</td>
         <td>POST</td>
        <td>create</td>
        <td>Create an event when authenticated as an user</td>
    </tr>
     <tr>
        <td>/show</td>
         <td>GET</td>
        <td>show</td>
        <td>Shows your profile</td>
    </tr>
    <tr>
        <td>/accept/{e_id}</td>
        <td>PATCH</td>
        <td>accept</td>
        <td>Accept/Reject an invitation</td>
    </tr>
    <tr>
        <td>/update/{e_id}</td>
        <td>PUT</td>
        <td>update</td>
        <td>Update an event</td>
    </tr>
    <tr>
        <td>/delete</td>
        <td>DELETE</td>
        <td>destroy</td>
        <td>Delete an event</td>
    </tr>
    <tr>
        <td>/remove/{id}</td>
        <td>DELETE</td>
        <td>remove</td>
        <td>Remove a member</td>
    </tr>
</table>

<h3>@eventsController</h3>
<h4> @index</h4>
<ul>
<li>you may only see your events which you have created and events you are invited to unless you are admin</li>
<li>find events you are a part of, show their information and whther they are accepted, rejected or pending</li>
</ul>

<h4>@create</h4>
<ul>
 <li>validate and create the event</li>
 <li>once you have created an event, you are also going to be added to the list of pending invitees</li>
<li>need the record of the latest event created by the current user so that we can set its status to pending in the invite_status table</li>
 </ul>

<h4> @accept</h4>
<ul>
<li>you may only accept the events you have been invited to unless you are admin</li>
<li>Admin requires a user_id request to know which member's status to alter</li>
<li>Check whether the event exists</li>
<li>Check if Admin is making changes</li>
<li>Check if the required user is invited to the event=>current user in case of non-admin</li>
<li>Update status and send mail to the required user</li>
</ul>

<h4>@invite</h4>
<ul>
<li>email of user required for invitation</li>
<li>Check if event exists</li>
<li>Is the user an admin or did the user create the event?</li>
<li>Does the user being invited exist?</li>
<li>Is the user already a member or an invitee of this current event?</li>
<li>Send email and invite</li>
</ul>

<h4>@showMems</h4>
<ul>
<li>Show all members of every event if user is Admin</li>
<li>Show members of all events user is a part of if user is non Admin</li>
</ul>

<h4>@remove</h4>
<ul>
<li>Email of member to be removed is required</li>
<li>Check if the event exists</li>
<li>Check if the event was created by the current user, or if the current user is an Admin</li>
<li>Check if the user to be deleted exists</li>
<li>Check if the user to be deleted is a part of the event</li>
</ul>

<h4>@update</h4>
<ul>
<li>Check if the event exists</li>
<li>Check if the event was created by the current user, or if the current user is an Admin</li>
<li>Mail everyone who is a member</li>
</ul>

<h4>@destroy</h4>
<ul>
<li>Check if the event exists</li>
<li>Check if the event was created by the current user, or if the current user is an Admin</li>
<li>Mail everyone who is a member and delete the event</li>
</ul>
