# event_api
<h3>Events Controller</h3>
<table>
    <tr>
    <th>URI</th>
    <th>Method</th>
    <th>Purpose</th>
    </tr> 
    <tr>
        <td>/</td>
        <td>index</td>
        <td>Viewing your events, Admin can see all events</td>
    </tr>
    <tr>
        <td>/mems</td>
        <td>showMems</td>
        <td>Viewing the members of events you are invited to, Admin can see members of all events</td>
    </tr>
     <tr>
        <td>/register</td>
        <td>register</td>
        <td>Register yourself as an user</td>
    </tr>
     <tr>
        <td>/inv/{e_id}</td>
        <td>invite</td>
        <td>Invite another user to an event, cannot invite if already present</td>
    </tr>
     <tr>
        <td>/create</td>
        <td>create</td>
        <td>Create an event when authenticated as an user</td>
    </tr>
     <tr>
        <td>/show</td>
        <td>show</td>
        <td>Shows your profile</td>
    </tr>
    <tr>
        <td>/accept/{e_id}</td>
        <td>accept</td>
        <td>Accept/Reject an invitation</td>
    </tr>
    <tr>
        <td>/update/{e_id}</td>
        <td>update</td>
        <td>Update an event</td>
    </tr>
    <tr>
        <td>/delete</td>
        <td>destroy</td>
        <td>Delete an event</td>
    </tr>
    <tr>
        <td>/remove/{id}</td>
        <td>remove</td>
        <td>Remove a member</td>
    </tr>
</table>
