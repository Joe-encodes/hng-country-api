# Koyeb Deployment Guide

## Quick Deploy Steps

### 1. Prepare Your Repository
- Ensure all code is committed and pushed to GitHub
- Verify `Dockerfile` exists in root directory
- Check that `.env.example` has all required variables

### 2. Create Koyeb App
1. Go to [Koyeb Console](https://app.koyeb.com)
2. Click "Create App"
3. Connect your GitHub repository
4. Select your `hng-country-api` repository

### 3. Configure Build Settings
- Build Method: Dockerfile
- Dockerfile Path: `./Dockerfile`
- Build Context: `.` (root directory)

### 4. Set Environment Variables
Click "Environment Variables" and add (replace with your secure values):

```
DB_CONNECTION=mysql
DB_HOST=<your-aiven-host>
DB_PORT=22962
DB_DATABASE=defaultdb
DB_USERNAME=<your-username>
DB_PASSWORD=<your-password>
APP_ENV=production
APP_DEBUG=false
APP_KEY=<base64-app-key>
```

### 5. Deploy
- Click "Deploy"
- Wait for build to complete (5-10 minutes)
- Note your app URL (e.g., `https://your-app-name.koyeb.app`)

### 6. Post-Deployment Setup
Once deployed, you need to run migrations:

1. **Option A: Via Koyeb Console**
   - Go to your app → "Runtime" tab
   - Click "Open Console"
   - Run: `php artisan migrate --force`

2. **Option B: Via SSH** (if enabled)
   ```bash
   ssh your-app-name@ssh.koyeb.app
   php artisan migrate --force
   php artisan storage:link
   ```

### 7. Test Your Deployment
```bash
# Test basic endpoint
curl https://your-app-name.koyeb.app/api/status

# Refresh countries data
curl -X POST https://your-app-name.koyeb.app/api/countries/refresh

# Get countries
curl https://your-app-name.koyeb.app/api/countries
```

## Troubleshooting

### Build Fails
- Check Dockerfile syntax
- Ensure all dependencies are in composer.json
- Verify PHP version compatibility

### Database Connection Issues
- Double-check environment variables
- Ensure Aiven database is accessible
- Check SSL requirements

### Migration Errors
- Run migrations manually via console
- Check database permissions
- Verify table structure

### Performance Issues
- Monitor resource usage in Koyeb dashboard
- Consider upgrading plan if needed
- Optimize database queries

## Production Tips

1. **Enable HTTPS**: Koyeb provides free SSL certificates
2. **Monitor Logs**: Use Koyeb's logging dashboard
3. **Set Up Alerts**: Configure monitoring for uptime
4. **Backup Strategy**: Regular database backups via Aiven
5. **Security**: Keep environment variables secure

## Cost Optimization

- Start with Koyeb's free tier
- Monitor usage and upgrade only when needed
- Use efficient database queries
- Implement caching where possible

Your API should now be live and accessible at your Koyeb URL! 🚀


