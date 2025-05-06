const express = require("express"); // Importer express pour créer un serveur HTTP
const mysql = require("mysql2/promise"); // Utilisation de mysql2 avec async/await
const { google } = require("googleapis");
const axios = require("axios");

// 🔹 Configuration MySQL
const dbConfig = {
  host: "localhost", // Adresse du serveur MySQL
  user: "maxime", // Nom d'utilisateur de la base de données
  password: "ProjetBTSciel!753", // Mot de passe de l'utilisateur
  database: "locabox", // Nom de la base de données
};

// Fonction pour récupérer un token OAuth2 depuis Firebase
async function getAccessToken() {
  const auth = new google.auth.GoogleAuth({
    keyFile: "./Firebase_admin_SDK.json", // Remplace par le bon chemin
    scopes: ["https://www.googleapis.com/auth/firebase.messaging"],
  });

  const client = await auth.getClient();
  const accessToken = await client.getAccessToken();

  return accessToken.token;
}

// Fonction pour envoyer une notification via FCM v1
async function sendFCMNotification(fcmToken, title, body) {
  try {
    const accessToken = await getAccessToken();
    const projectId = "locabox-bfb0f"; // Remplace par ton ID Firebase

    const url = `https://fcm.googleapis.com/v1/projects/${projectId}/messages:send`;
    const headers = {
      Authorization: `Bearer ${accessToken}`,
      "Content-Type": "application/json",
    };

    const data = {
      message: {
        token: fcmToken,
        notification: {
          title: title,
          body: body,
        },
      },
    };

    const response = await axios.post(url, data, { headers });
    return response.data;
  } catch (error) {
    console.error("Erreur lors de l'envoi de la notification:", error);
    throw error;
  }
}

// Fonction pour récupérer le token FCM d'un utilisateur
async function getFCMToken(userId) {
  let connection;
  try {
    connection = await mysql.createConnection(dbConfig); //Connexion correcte

    const [rows] = await connection.execute(
      "SELECT fcm FROM user_box WHERE id_user_box = ?",
      [userId]
    );

    if (rows.length > 0) {
      console.log("Token FCM trouvé:", rows[0].fcm);
      return rows[0].fcm;
    } else {
      console.warn("Aucun token FCM trouvé pour l'utilisateur ID:", userId);
      return null;
    }
  } catch (error) {
    console.error("Erreur MySQL:", error);
    return null;
  } finally {
    if (connection) await connection.end(); //Ferme la connexion proprement
  }
}

// Créer l'application Express
const app = express();
app.use(express.json()); // Middleware pour parser les corps de requêtes en JSON

// Définir un point d'API POST pour envoyer une notification
app.post("/send-notification", async (req, res) => {
  const { userId, title, body } = req.body; // Extraire les paramètres du corps de la requête

  if (!userId || !title || !body) {
    return res
      .status(400)
      .json({ error: "userId, title, and body are required" });
  }

  try {
    // Récupérer le token FCM pour l'utilisateur
    const fcmToken = await getFCMToken(userId);

    if (!fcmToken) {
      return res
        .status(404)
        .json({ error: "FCM token not found for this user" });
    }

    // Envoyer la notification via FCM
    const response = await sendFCMNotification(fcmToken, title, body);
    return res
      .status(200)
      .json({ message: "Notification sent successfully", response });
  } catch (error) {
    console.error("Error sending notification:", error);
    return res
      .status(500)
      .json({ error: "An error occurred while sending the notification" });
  }
});

// Lancer le serveur
const port = 3000;
app.listen(port, () => {
  console.log(`Server running on http://localhost:${port}`);
});
