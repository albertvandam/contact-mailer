# Contact form mailer

CORS Compliant contact form mailer

## Usage

* Install dependencies
* Set configuration
* Set email template
* POST your form to the _contact.php_ script. **Note** The script expects JSON data as the RAW HTTP Content.

The JSON structure must contain the following properties:
* name - Name of the person completing your form
* email - Email address of the person
* message - Message provided by the person

Optionally, with ReCaptcha support enabled, a __captcha__ property containing the verification key.

```json
{
  "name": "My Name",
  "email": "name@myisp.com",  
  "message": "My messsage"
}
```

## Configuration

**Copy** _config/contact.config.php.dist_ to _config/contact.config.php_ and edit to suit your neeeds.

| Configuration Key | Description | 
|-------------------|-------------|
| origin | Regular expression your domain. If this fails, the emails will not be sent |
| recaptcha | Google ReCaptcha support |
| subject | Email subject |
| sender | Address where email will come from |
| recipient | Address where email should go |
| smtp | SMTP Server settings |

### ReCaptcha configuration

The ReCaptcha configuration is set in the __recaptcha__ key:

| Sub key | Description |
|---------|-------------|
| enabled | Support enabled/disabled |
| secret | Secret provided by Google |

### SMTP configuration

**Note** The connection to the SMTP server will always be made with SSL and authentication.

The SMTP configuration is set in the __smtp__ key:

| Sub key | Description |
|---------|-------------|
| server | SMTP Server address |
| port | SMTP Server port |
| user | User to authenticate with |
| password | Password to authenticate with |
 
 
