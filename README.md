# FoodRescueConnect (FoodShare) 🍲🤝

FoodRescueConnect is a web platform designed to reduce food waste and help communities by connecting individuals and businesses with surplus food to local charities and orphanages. 

## 🌟 Features
- **Three User Roles:**
  - **Donors:** Can easily list surplus food items, specify pickup locations, and approve charity requests.
  - **Charities:** Can browse available food listings and place requests. New charities undergo an admin verification process to ensure trust and safety.
  - **Admin:** A dedicated dashboard to oversee the platform, verify charities via their PAN Card and organization details, and monitor overall donation activity.
- **Dynamic Connections:** Donors and charities can directly connect to coordinate pickups.
- **Cloud-Ready:** Completely configured to be deployed on Vercel utilizing a Serverless PHP environment and Cloud SQL databases (like TiDB).

## 🛠️ Tech Stack
- **Frontend:** HTML, Tailwind CSS (for modern, responsive styling)
- **Backend:** Core PHP
- **Database:** MySQL (Configured for TiDB Serverless Cloud DB)
- **Hosting/Deployment:** Vercel (using the `vercel-php` community builder)

## 🚀 Deployment on Vercel
1. Import this repository into Vercel.
2. Under the Project Settings > Environment Variables, add the following to connect to your TiDB Cloud Database:
   - `DB_HOST`
   - `DB_PORT` (typically 4000 for TiDB)
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `DB_DATABASE`
3. Deploy the project.
4. Once deployed, visit `https://<your-vercel-domain>.vercel.app/config/init_db.php` in your browser one time to automatically build the database tables.

---
*Built to make a difference in the community.*
