const { Client, GatewayIntentBits, REST, Routes, SlashCommandBuilder } = require("discord.js");
const mysql = require("mysql2/promise");
const crypto = require("crypto");

// Configuration
const config = {
	// Serveur 1 OPTIGF
	leaveChannelId1: process.env.DISCORD_LEAVE_CHANNEL_ID_1,
	leaveGuildId1: process.env.DISCORD_LEAVE_GUILD_ID_1,
	// Serveur 2 FLEURDELYS
	leaveChannelId2: process.env.DISCORD_LEAVE_CHANNEL_ID_2,
	leaveGuildId2: process.env.DISCORD_LEAVE_GUILD_ID_2,

	token: process.env.DISCORD_TOKEN,
	adminRole: process.env.DISCORD_ADMIN_ROLE,
	injectionRole: process.env.DISCORD_INJECTION_ROLE,
	registerChannelId: process.env.DISCORD_REGISTER_CHANNEL_ID,
	historyChannelId: process.env.DISCORD_HISTORY_CHANNEL_ID,
	database: {
		host: process.env.DB_HOST,
		user: process.env.DB_USER,
		password: process.env.DB_PASSWORD,
		database: process.env.DB_NAME,
	},
};

// Client Discord
const client = new Client({
	intents: [GatewayIntentBits.Guilds, GatewayIntentBits.DirectMessages, GatewayIntentBits.GuildMessages, GatewayIntentBits.MessageContent, GatewayIntentBits.GuildMembers],
});

// Définition des commandes
const commands = [
	new SlashCommandBuilder().setName("register-carso-key").setDescription("Créer votre clé Carso (à utiliser dans le salon dédié)"),

	new SlashCommandBuilder()
		.setName("renew-carso-key")
		.setDescription("Renouveler une clé Carso")
		.addStringOption((option) => option.setName("target").setDescription("[ADMIN] 'all' ou un username Discord (vide = vous-même)").setRequired(false)),

	new SlashCommandBuilder()
		.setName("resend-carso-key")
		.setDescription("Renvoyer une clé Carso existante")
		.addStringOption((option) => option.setName("target").setDescription("[ADMIN] Un username Discord (vide = vous-même)").setRequired(false)),

	new SlashCommandBuilder().setName("list-users").setDescription("[ADMIN] Lister tous les utilisateurs en base"),

	new SlashCommandBuilder().setName("sync-nicknames").setDescription("[ADMIN] Synchroniser tous les nicknames du serveur avec les usernames Discord"),

	new SlashCommandBuilder().setName("help").setDescription("Afficher les commandes disponibles"),

	new SlashCommandBuilder()
		.setName("msg")
		.setDescription("Envoyer un message dans le salon")
		.addStringOption((option) => option.setName("message").setDescription("Le message à envoyer").setRequired(true)),
];

// Pool de connexions DB
let dbPool;

async function initDB() {
	dbPool = mysql.createPool({
		...config.database,
		waitForConnections: true,
		connectionLimit: 10,
		queueLimit: 0,
	});
}

// Utilitaires
function generateKey() {
	const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	return Array.from(crypto.randomBytes(24))
		.map((byte) => chars[byte % chars.length])
		.join("");
}

function isAdmin(member) {
	if (!member) {
		console.log("isAdmin: member est null");
		return false;
	}
	if (!config.adminRole) {
		console.warn("isAdmin: DISCORD_ADMIN_ROLE non configuré");
		return false;
	}
	const hasRole = member.roles.cache.has(config.adminRole);
	console.log(`isAdmin: user=${member.user.username}, adminRole=${config.adminRole}, hasRole=${hasRole}`);
	return hasRole;
}

async function sendDM(userId, message) {
	try {
		const user = await client.users.fetch(userId);
		await user.send(message);
		return true;
	} catch (error) {
		console.error(`Erreur envoi DM à ${userId}:`, error.message);
		return false;
	}
}

async function logHistory(message) {
	if (!config.historyChannelId) return;
	try {
		const channel = await client.channels.fetch(config.historyChannelId);
		await channel.send(message);
	} catch (error) {
		console.error("Erreur log historique:", error.message);
	}
}

async function resolveUserInput(input) {
	try {
		// Cas 1: C'est une mention Discord (<@123456> ou <@!123456>)
		const mentionMatch = input.match(/^<@!?(\d+)>$/);
		if (mentionMatch) {
			const userId = mentionMatch[1];
			try {
				const user = await client.users.fetch(userId);
				return user; // Retourne l'objet User Discord
			} catch (error) {
				console.error(`Impossible de fetch l'utilisateur ID ${userId}:`, error.message);
				return null;
			}
		}

		// Cas 2: C'est un username en texte brut
		return await getUserFromUsername(input);
	} catch (error) {
		console.error(`Erreur resolveUserInput pour ${input}:`, error.message);
		return null;
	}
}

async function getUserFromUsername(username) {
	try {
		let user = client.users.cache.find((u) => u.username === username);
		if (user) return user;

		for (const guild of client.guilds.cache.values()) {
			try {
				const members = await guild.members.fetch({ query: username, limit: 1 });
				if (members.size > 0) return members.first().user;
			} catch (error) {
				continue;
			}
		}
		return null;
	} catch (error) {
		console.error(`Erreur getUserFromUsername pour ${username}:`, error.message);
		return null;
	}
}

async function getUserMention(username) {
	try {
		const user = await getUserFromUsername(username);
		return user ? `<@${user.id}>` : username;
	} catch (error) {
		console.error(`Erreur getUserMention pour ${username}:`, error.message);
		return username;
	}
}

async function assignInjectionRole(guildId, userId) {
	if (!config.injectionRole) return false;
	try {
		const guild = await client.guilds.fetch(guildId);
		const member = await guild.members.fetch(userId);
		await member.roles.add(config.injectionRole);
		return true;
	} catch (error) {
		console.error(`Erreur attribution rôle injection:`, error.message);
		return false;
	}
}

async function autoSyncUserNickname(member) {
	if (!member || member.user.bot) return;

	const currentNickname = member.nickname;
	const username = member.user.username;

	// Si déjà sync, ne rien faire
	if (currentNickname === username) return;

	// Skip le propriétaire du serveur
	if (member.guild && member.id === member.guild.ownerId) return;

	// Skip si pas les permissions
	if (!member.manageable) return;

	try {
		// Reset puis set pour forcer le changement
		if (currentNickname !== null) {
			await member.setNickname(null);
		}
		await member.setNickname(username);
		console.log(`🔄 Auto-sync: ${currentNickname || "(aucun)"} → ${username}`);
	} catch (error) {
		console.error(`⚠️ Auto-sync échoué pour ${username}:`, error.message);
	}
}

// COMMANDES

async function handleRegister(interaction) {
	if (config.registerChannelId && interaction.channelId !== config.registerChannelId) {
		return await interaction.reply({
			content: `❌ Cette commande doit être utilisée dans <#${config.registerChannelId}>`,
			flags: 64,
		});
	}

	const username = interaction.user.username;
	const userId = interaction.user.id;

	try {
		const [existing] = await dbPool.execute("SELECT account_key FROM users WHERE discord = ?", [username]);

		if (existing.length > 0) {
			return await interaction.reply({
				content:
					"⚠️ Attention ⚠️\nVous avez déjà un compte ! Utilisez `/resend-carso-key` pour que le bot vous renvoie votre clé.\n\nSinon, utilisez `/renew-carso-key` pour en générer une nouvelle.",
				flags: 64,
			});
		}

		const newKey = generateKey();
		await dbPool.execute("INSERT INTO users (discord, account_key, vip) VALUES (?, ?, 0)", [username, newKey]);

		await assignInjectionRole(interaction.guildId, userId);

		const dmMessage =
			`https://optigf.eu/\n\n` +
			`**Bienvenue sur OptiGF !** Votre Clé de Carso a bien été générée, la voici :\n` +
			`||\`\`\`${newKey}\`\`\`||\n` +
			`Vous pouvez désormais créer votre compte et vous connecter sur **[cette page](https://optigf.eu/#account)**\n` +
			`Enjoy 😎\n\n` +
			`⚠️ **RAPPEL** ⚠️\n` +
			`En utilisant nos services, vous :\n` +
			`- acceptez les [Conditions Générales d'Utilisation](https://optigf.eu/legal.php#cgu)\n` +
			`- certifiez avoir pris connaissance de notre [Politique de Confidentialité](https://optigf.eu/legal.php#privacy)\n\n` +
			`Les modalités de contact sont définies sur la page des [Mentions Légales](https://optigf.eu/legal.php).` +
			`\n\n-`;

		const sent = await sendDM(userId, dmMessage);

		await interaction.reply({
			content: sent ? "✅ Votre compte a été créé ! Vérifiez vos MPs." : `⚠️ Compte créé mais échec envoi MP.\nClé : \`${newKey}\``,
			flags: 64,
		});

		const mention = await getUserMention(username);
		await logHistory(`🆕 ${mention} a créé son compte\nClé : \`${newKey}\``);
	} catch (error) {
		console.error("Erreur register:", error);
		await interaction.reply({ content: "❌ Erreur lors de la création du compte.", flags: 64 });
	}
}

async function handleRenew(interaction) {
	const target = interaction.options.getString("target");
	const isAdminUser = interaction.member ? isAdmin(interaction.member) : false;

	if (!target) {
		const username = interaction.user.username;
		const userId = interaction.user.id;

		try {
			const [user] = await dbPool.execute("SELECT account_key FROM users WHERE discord = ?", [username]);

			if (user.length === 0) {
				return await interaction.reply({
					content: "❌ Vous n'avez pas de compte. Utilisez `/register-carso-key`.",
					flags: 64,
				});
			}

			const newKey = generateKey();
			await dbPool.execute("UPDATE users SET account_key = ? WHERE discord = ?", [newKey, username]);

			const dmMessage =
				`https://optigf.eu/\n\n` +
				`**🔄 Renouvellement de votre Clé de Carso 🔄**\nVous avez demandé un renouvellement de votre clé. Voici la nouvelle qui devra maintenant être utilisée :\n` +
				`||\`\`\`${newKey}\`\`\`||\n` +
				`À utiliser sur la **[page de connexion](https://optigf.eu/#account)** ou dans notre application.\n` +
				`Enjoy 😎\n\n` +
				`⚠️ **RAPPEL** ⚠️\n` +
				`En utilisant nos services, vous :\n` +
				`- acceptez les [Conditions Générales d'Utilisation](https://optigf.eu/legal.php#cgu)\n` +
				`- certifiez avoir pris connaissance de notre [Politique de Confidentialité](https://optigf.eu/legal.php#privacy)\n\n` +
				`Les modalités de contact sont définies sur la page des [Mentions Légales](https://optigf.eu/legal.php).` +
				`\n\n--`;

			const sent = await sendDM(userId, dmMessage);

			await interaction.reply({
				content: sent ? "✅ Nouvelle clé envoyée en MP !" : `⚠️ Échec envoi MP.\nClé : \`${newKey}\``,
				flags: 64,
			});

			const mention = await getUserMention(username);
			await logHistory(`🔄 ${mention} a renouvelé sa clé\nNouvelle : \`${newKey}\``);
		} catch (error) {
			console.error("Erreur renew:", error);
			await interaction.reply({ content: "❌ Erreur lors du renouvellement.", flags: 64 });
		}
		return;
	}

	if (!isAdminUser) {
		return await interaction.reply({
			content: "❌ Permission refusée.",
			flags: 64,
		});
	}

	await interaction.deferReply({ flags: 64 });

	if (target.toLowerCase() === "all") {
		try {
			const [users] = await dbPool.execute("SELECT discord FROM users");

			let success = 0,
				fail = 0;
			const processedUserIds = new Set(); // Pour éviter les doublons

			await logHistory(`🔄 **Renouvellement massif** par ${interaction.user.tag}`);

			for (const row of users) {
				const username = row.discord;
				const newKey = generateKey();

				try {
					const discordUser = await getUserFromUsername(username);

					// Si l'utilisateur Discord n'existe pas, on skip
					if (!discordUser) {
						fail++;
						const mention = await getUserMention(username);
						await logHistory(`❌ ${mention} : utilisateur introuvable sur Discord`);
						// On met quand même à jour la clé en base pour cet ancien username
						await dbPool.execute("UPDATE users SET account_key = ? WHERE discord = ?", [newKey, username]);
						continue;
					}

					// Vérifier si on a déjà traité cet utilisateur Discord (même ID)
					if (processedUserIds.has(discordUser.id)) {
						console.log(`⚠️ Doublon détecté: ${username} pointe vers le même user Discord que précédemment (ID: ${discordUser.id})`);
						// On met à jour la clé en base mais on ne renvoie pas de MP
						await dbPool.execute("UPDATE users SET account_key = ? WHERE discord = ?", [newKey, username]);
						await logHistory(`⚠️ ${username} : doublon ignoré (même user Discord qu'une entrée précédente)`);
						continue;
					}

					// Marquer cet ID comme traité
					processedUserIds.add(discordUser.id);

					// Mettre à jour la clé en base
					await dbPool.execute("UPDATE users SET account_key = ? WHERE discord = ?", [newKey, username]);

					const dmMessage =
						`https://optigf.eu/\n\n` +
						`**🔄 Renouvellement de votre Clé de Carso 🔄**\nNous avons dû renouveler votre clé. La voici :\n` +
						`||\`\`\`${newKey}\`\`\`||\n` +
						`À utiliser sur la **[page de connexion](https://optigf.eu/#account)** ou dans notre application.\n` +
						`Enjoy 😎\n\n` +
						`⚠️ **RAPPEL** ⚠️\n` +
						`En utilisant nos services, vous :\n` +
						`- acceptez les [Conditions Générales d'Utilisation](https://optigf.eu/legal.php#cgu)\n` +
						`- certifiez avoir pris connaissance de notre [Politique de Confidentialité](https://optigf.eu/legal.php#privacy)\n\n` +
						`Les modalités de contact sont définies sur la page des [Mentions Légales](https://optigf.eu/legal.php).` +
						`\n\n---`;

					const sent = await sendDM(discordUser.id, dmMessage);

					const mention = await getUserMention(username);
					if (sent) {
						success++;
						await logHistory(`✅ ${mention} : \`${newKey}\``);
					} else {
						fail++;
						await logHistory(`❌ ${mention} : échec MP - \`${newKey}\``);
					}

					await new Promise((resolve) => setTimeout(resolve, 100));
				} catch (error) {
					fail++;
					const mention = await getUserMention(username);
					await logHistory(`❌ ${mention} : ${error.message}`);
				}
			}

			await interaction.editReply({ content: `✅ Terminé\n• Succès : ${success}\n• Échecs : ${fail}` });
			await logHistory(`📊 Résumé : ${success} succès, ${fail} échecs`);
		} catch (error) {
			console.error("Erreur renew all:", error);
			await interaction.editReply({ content: "❌ Erreur renouvellement massif." });
		}
		return;
	}

	try {
		// Essayer de résoudre la mention ou le username
		const targetUser = await resolveUserInput(target);
		let targetUsername;

		if (targetUser) {
			// Utilisateur trouvé sur Discord, utiliser son username actuel
			targetUsername = targetUser.username;
		} else {
			// Pas trouvé sur Discord, utiliser le target comme username brut en base
			targetUsername = target;
		}

		const [user] = await dbPool.execute("SELECT account_key FROM users WHERE discord = ?", [targetUsername]);

		if (user.length === 0) {
			return await interaction.editReply({ content: `❌ Aucun compte pour ${targetUsername}` });
		}

		const newKey = generateKey();
		await dbPool.execute("UPDATE users SET account_key = ? WHERE discord = ?", [newKey, targetUsername]);

		const dmMessage =
			`https://optigf.eu/\n\n` +
			`**🔄 Renouvellement de votre Clé de Carso 🔄**\nNous avons dû renouveler votre clé. La voici :\n` +
			`||\`\`\`${newKey}\`\`\`||\n` +
			`À utiliser sur la **[page de connexion](https://optigf.eu/#account)** ou dans notre application.\n` +
			`Enjoy 😎\n\n` +
			`⚠️ **RAPPEL** ⚠️\n` +
			`En utilisant nos services, vous :\n` +
			`- acceptez les [Conditions Générales d'Utilisation](https://optigf.eu/legal.php#cgu)\n` +
			`- certifiez avoir pris connaissance de notre [Politique de Confidentialité](https://optigf.eu/legal.php#privacy)\n\n` +
			`Les modalités de contact sont définies sur la page des [Mentions Légales](https://optigf.eu/legal.php).` +
			`\n\n----`;

		let sent = false;
		if (targetUser) {
			sent = await sendDM(targetUser.id, dmMessage);
		}

		await interaction.editReply({
			content: sent
				? `✅ Clé renouvelée pour ${targetUsername} (MP envoyé)`
				: `⚠️ Clé renouvelée pour ${targetUsername}${targetUser ? " mais échec MP" : " (utilisateur non trouvé sur Discord)"}\nClé : \`${newKey}\``,
		});

		const mention = await getUserMention(targetUsername);
		await logHistory(`🔄 ${await getUserMention(interaction.user.username)} a renouvelé la clé de ${mention}\nClé : \`${newKey}\``);
	} catch (error) {
		console.error("Erreur renew user:", error);
		await interaction.editReply({ content: "❌ Erreur renouvellement." });
	}
}

async function handleResend(interaction) {
	const target = interaction.options.getString("target");
	const isAdminUser = interaction.member ? isAdmin(interaction.member) : false;

	// Sans argument = renvoi personnel
	if (!target) {
		const username = interaction.user.username;
		const userId = interaction.user.id;

		try {
			const [user] = await dbPool.execute("SELECT account_key FROM users WHERE discord = ?", [username]);

			if (user.length === 0) {
				return await interaction.reply({
					content: "❌ Vous n'avez pas de compte. Utilisez `/register-carso-key`.",
					flags: 64,
				});
			}

			const key = user[0].account_key;

			const dmMessage =
				`https://optigf.eu/\n\n` +
				`Voici votre clé de Carso :\n` +
				`||\`\`\`${key}\`\`\`||\n` +
				`Elle est à utiliser sur la **[page de connexion du site](https://optigf.eu/#account)** ou dans notre application.\n` +
				`Enjoy 😎\n\n` +
				`⚠️ **RAPPEL** ⚠️\n` +
				`En utilisant nos services, vous :\n` +
				`- acceptez les [Conditions Générales d'Utilisation](https://optigf.eu/legal.php#cgu)\n` +
				`- certifiez avoir pris connaissance de notre [Politique de Confidentialité](https://optigf.eu/legal.php#privacy)\n\n` +
				`Les modalités de contact sont définies sur la page des [Mentions Légales](https://optigf.eu/legal.php).` +
				`\n\n-----`;

			const sent = await sendDM(userId, dmMessage);

			await interaction.reply({
				content: sent ? "✅ Clé envoyée en MP !" : `⚠️ Échec envoi MP.\nClé : \`${key}\``,
				flags: 64,
			});
		} catch (error) {
			console.error("Erreur resend:", error);
			await interaction.reply({ content: "❌ Erreur lors du renvoi.", flags: 64 });
		}
		return;
	}

	// Avec argument = admin uniquement
	if (!isAdminUser) {
		return await interaction.reply({ content: "❌ Permission refusée.", flags: 64 });
	}

	await interaction.deferReply({ flags: 64 });

	try {
		// Résoudre la mention ou le username
		const targetUser = await resolveUserInput(target);
		let targetUsername;

		if (targetUser) {
			targetUsername = targetUser.username;
		} else {
			targetUsername = target;
		}

		const [user] = await dbPool.execute("SELECT account_key FROM users WHERE discord = ?", [targetUsername]);

		if (user.length === 0) {
			return await interaction.editReply({ content: `❌ Aucun compte pour ${targetUsername}` });
		}

		const key = user[0].account_key;

		const dmMessage =
			`https://optigf.eu/\n\n` +
			`Vous avez demandé à recevoir de nouveau votre clé Carso. La voici :\n` +
			`||\`\`\`${key}\`\`\`||\n` +
			`À utiliser sur la **[page de connexion](https://optigf.eu/#account)** ou dans notre application.\n` +
			`Enjoy 😎\n\n` +
			`⚠️ **RAPPEL** ⚠️\n` +
			`En utilisant nos services, vous :\n` +
			`- acceptez les [Conditions Générales d'Utilisation](https://optigf.eu/legal.php#cgu)\n` +
			`- certifiez avoir pris connaissance de notre [Politique de Confidentialité](https://optigf.eu/legal.php#privacy)\n\n` +
			`Les modalités de contact sont définies sur la page des [Mentions Légales](https://optigf.eu/legal.php).` +
			`\n\n------`;

		let sent = false;
		if (targetUser) {
			sent = await sendDM(targetUser.id, dmMessage);
		}

		await interaction.editReply({
			content: sent ? `✅ Clé renvoyée à ${targetUsername}` : `⚠️ Clé pour ${targetUsername}${targetUser ? " - échec MP" : " (utilisateur non trouvé sur Discord)"}\nClé : \`${key}\``,
		});

		const mentionTarget = await getUserMention(targetUsername);
		const mentionAdmin = await getUserMention(interaction.user.username);
		await logHistory(`📤 ${mentionAdmin} a renvoyé la clé à ${mentionTarget}`);
	} catch (error) {
		console.error("Erreur resend user:", error);
		await interaction.editReply({ content: "❌ Erreur renvoi." });
	}
}

async function handleListUsers(interaction) {
	if (!interaction.member || !isAdmin(interaction.member)) {
		return await interaction.reply({ content: "❌ Permission refusée.", flags: 64 });
	}

	await interaction.deferReply({ flags: 64 });

	try {
		const [users] = await dbPool.execute("SELECT discord, vip FROM users ORDER BY discord");

		if (users.length === 0) {
			return await interaction.editReply({ content: "ℹ️ Aucun utilisateur." });
		}

		let message = `**Liste des utilisateurs en base de données (${users.length})**\n\n`;

		for (const user of users) {
			const vipIcon = user.vip === 1 ? "- ✨" : "";
			const mention = await getUserMention(user.discord);
			message += `• ${mention} ${vipIcon}\n`;
		}

		if (message.length > 2000) {
			const chunks = message.match(/[\s\S]{1,2000}/g);
			await interaction.editReply({ content: chunks[0] });
			for (let i = 1; i < chunks.length; i++) {
				await interaction.followUp({ content: chunks[i], flags: 64 });
			}
		} else {
			await interaction.editReply({ content: message });
		}
	} catch (error) {
		console.error("Erreur list:", error);
		await interaction.editReply({ content: "❌ Erreur récupération utilisateurs." });
	}
}

async function handleSyncNicknames(interaction) {
	if (!interaction.member || !isAdmin(interaction.member)) {
		return await interaction.reply({ content: "❌ Permission refusée.", flags: 64 });
	}

	await interaction.deferReply({ flags: 64 });

	try {
		const guild = interaction.guild;
		if (!guild) {
			return await interaction.editReply({ content: "❌ Cette commande doit être utilisée dans un serveur." });
		}

		// Récupérer tous les membres du serveur
		await guild.members.fetch();
		const members = guild.members.cache;

		let success = 0;
		let fail = 0;
		let skipped = 0;
		const errors = [];

		await interaction.editReply({ content: `🔄 Synchronisation en cours pour ${members.size} membres...` });

		for (const [memberId, member] of members) {
			const currentNickname = member.nickname;
			const username = member.user.username;

			// Skip les bots
			if (member.user.bot) {
				skipped++;
				continue;
			}

			// Skip le propriétaire du serveur
			if (member.id === guild.ownerId) {
				skipped++;
				console.log(`⚠️ Skip propriétaire : ${username}`);
				continue;
			}

			// Skip si pas les permissions
			if (!member.manageable) {
				skipped++;
				console.log(`⚠️ Skip (permissions) : ${username}`);
				continue;
			}

			try {
				// Si le nickname est déjà exactement le username, skip
				if (currentNickname === username) {
					console.log(`✓ Déjà OK : ${username}`);
					success++;
					continue;
				}

				// Stratégie : reset puis set pour forcer le changement
				// Étape 1 : Reset le nickname (mettre à null)
				if (currentNickname !== null) {
					await member.setNickname(null, "Reset avant sync");
					await new Promise((resolve) => setTimeout(resolve, 500));
				}

				// Étape 2 : Set le username comme nickname
				await member.setNickname(username, "Sync nickname = username");
				success++;
				console.log(`✅ ${currentNickname || "(aucun)"} → ${username}`);

				// Délai pour éviter le rate limiting
				await new Promise((resolve) => setTimeout(resolve, 1000));
			} catch (error) {
				fail++;
				const errorMsg = `${username}: ${error.message}`;
				errors.push(errorMsg);
				console.error(`❌ ${errorMsg}`);
			}
		}

		let summary = `✅ **Synchronisation terminée**\n\n` + `• Modifiés : ${success}\n` + `• Échecs : ${fail}\n` + `• Skippés : ${skipped}\n` + `• Total : ${members.size}`;

		// Ajouter les erreurs si présentes
		if (errors.length > 0 && errors.length <= 5) {
			summary += `\n\n**Erreurs :**\n${errors.map((e) => `• ${e}`).join("\n")}`;
		} else if (errors.length > 5) {
			summary += `\n\n**Erreurs :** ${errors.length} membres (voir logs console)`;
		}

		await interaction.editReply({ content: summary });

		await logHistory(`🔄 **${interaction.user.tag}** a synchronisé les nicknames\n` + `Modifiés : ${success} | Échecs : ${fail} | Skippés : ${skipped}`);
	} catch (error) {
		console.error("Erreur sync nicknames:", error);
		await interaction.editReply({ content: "❌ Erreur lors de la synchronisation." });
	}
}

async function handleHelp(interaction) {
	const isAdminUser = interaction.member ? isAdmin(interaction.member) : false;

	let help = "**BOT CARSO - Liste des commandes disponibles**\n\n";

	help += "**⚙️ Commandes générales ⚙️**\n";
	help += "• **`/help`** - Afficher l'aide\n";
	help += "• **`/msg <message>`** - Envoyer un message dans le salon courant via le bot\n\n";

	help += `**👥 Commandes utilisateur👥** *à utiliser dans <#${config.registerChannelId}>*\n`;
	help += "• **`/register-carso-key`** - Générer une clé et se la faire envoyer par MP\n";
	help += "• **`/resend-carso-key`** - Recevoir à nouveau sa clé en MP\n";
	help += "• **`/renew-carso-key`** - Générer une nouvelle clé pour soi-même et se la faire envoyer par MP\n";

	if (isAdminUser) {
		help += `\n**🛡️ Commandes admin 🛡️** *à utiliser dans <#${config.registerChannelId}>*\n`;
		help += "• **`/list-users`** - Lister les utilisateurs en base de données\n";
		help += "• **`/sync-nicknames`** - Change le pseudo de tous les utilisateurs pour qu'ils soient leur username (évite les confusions)\n";
		help += "• **`/resend-carso-key <username>`** - Renvoyer la clé d'un utilisateur\n";
		help += "• **`/renew-carso-key all`** - Renouveler la clé de tous les utilisateurs\n";
		help += "• **`/renew-carso-key <username>`** - Renouveler la clé d'un utilisateur en particulier\n";
	}

	await interaction.reply({ content: help, flags: 64 });
}

async function handleMsg(interaction) {
	const message = interaction.options.getString("message");

	try {
		await interaction.deferReply();
		await interaction.deleteReply();
		await interaction.channel.send(message);
	} catch (error) {
		console.error("Erreur msg:", error);
		if (!interaction.replied) {
			await interaction.reply({ content: "❌ Erreur envoi message.", flags: 64 });
		}
	}
}

// GESTION DES INTERACTIONS

client.on("interactionCreate", async (interaction) => {
	if (!interaction.isCommand()) return;

	// AUTO-SYNC du nickname avant chaque commande
	if (interaction.member) {
		await autoSyncUserNickname(interaction.member);
	}

	try {
		switch (interaction.commandName) {
			case "register-carso-key":
				await handleRegister(interaction);
				break;
			case "renew-carso-key":
				await handleRenew(interaction);
				break;
			case "resend-carso-key":
				await handleResend(interaction);
				break;
			case "list-users":
				await handleListUsers(interaction);
				break;
			case "sync-nicknames":
				await handleSyncNicknames(interaction);
				break;
			case "help":
				await handleHelp(interaction);
				break;
			case "msg":
				await handleMsg(interaction);
				break;
		}
	} catch (error) {
		console.error(`Erreur ${interaction.commandName}:`, error);
		const reply = { content: "❌ Erreur.", flags: 64 };
		if (interaction.deferred || interaction.replied) {
			await interaction.editReply(reply);
		} else {
			await interaction.reply(reply);
		}
	}
});

// INITIALISATION

client.once("ready", async () => {
	console.log(`✅ Bot connecté : ${client.user.tag}`);

	await initDB();
	console.log("✅ DB initialisée");

	try {
		const rest = new REST({ version: "10" }).setToken(config.token);
		await rest.put(Routes.applicationCommands(client.user.id), { body: commands });
		console.log("✅ Commandes enregistrées");
	} catch (error) {
		console.error("❌ Erreur commandes:", error);
	}
});

client.on("error", (error) => console.error("Erreur client:", error));
process.on("unhandledRejection", (error) => console.error("Promesse rejetée:", error));




// Événement quand un membre rejoint le serveur
client.on("guildMemberAdd", async (member) => {
	// Déterminer quel serveur et quel salon utiliser
	let targetChannelId = null;
	
	if (config.leaveGuildId1 && member.guild.id === config.leaveGuildId1) {
		targetChannelId = config.leaveChannelId1;
	} else if (config.leaveGuildId2 && member.guild.id === config.leaveGuildId2) {
		targetChannelId = config.leaveChannelId2;
	}
	
	// Si ce n'est pas un des serveurs surveillés ou pas de salon configuré
	if (!targetChannelId) return;

	try {
		const channel = await client.channels.fetch(targetChannelId);
		const username = member.user.username;
		const userId = member.user.id;
		
		await channel.send(
			`🎉 **${username}** (<@${userId}>) a rejoint le serveur **${member.guild.name}** !`
		);
		
		console.log(`📥 Notification envoyée : ${username} a rejoint ${member.guild.name}`);
	} catch (error) {
		console.error("Erreur notification arrivée:", error.message);
	}
});


// Événement quand un membre quitte le serveur
client.on("guildMemberRemove", async (member) => {
	// Déterminer quel serveur et quel salon utiliser
	let targetChannelId = null;
	
	if (config.leaveGuildId1 && member.guild.id === config.leaveGuildId1) {
		targetChannelId = config.leaveChannelId1;
	} else if (config.leaveGuildId2 && member.guild.id === config.leaveGuildId2) {
		targetChannelId = config.leaveChannelId2;
	}
	
	// Si ce n'est pas un des serveurs surveillés ou pas de salon configuré
	if (!targetChannelId) return;

	try {
		// Attendre un peu pour laisser le temps aux audit logs de se mettre à jour
		await new Promise(resolve => setTimeout(resolve, 1000));
		
		const username = member.user.username;
		const userId = member.user.id;
		let message = "";
		let reason = "départ volontaire";
		
		// Vérifier les kicks
		const auditLogsKick = await member.guild.fetchAuditLogs({
			limit: 1,
			type: 20, // MEMBER_KICK
		});
		const kickLog = auditLogsKick.entries.first();
		const isRecentKick = kickLog && 
			kickLog.target.id === member.user.id && 
			Date.now() - kickLog.createdTimestamp < 5000;
		
		// Vérifier les bans
		const auditLogsBan = await member.guild.fetchAuditLogs({
			limit: 1,
			type: 22, // MEMBER_BAN_ADD
		});
		const banLog = auditLogsBan.entries.first();
		const isRecentBan = banLog && 
			banLog.target.id === member.user.id && 
			Date.now() - banLog.createdTimestamp < 5000;
		
		// Construire le message selon le type de départ
		if (isRecentBan) {
			const executor = banLog.executor;
			const banReason = banLog.reason || "Aucune raison spécifiée";
			message = `🔨 **${username}** (<@${userId}>) a été **banni** par **${executor.username}**\nRaison : ${banReason}`;
			reason = "ban";
		} else if (isRecentKick) {
			const executor = kickLog.executor;
			const kickReason = kickLog.reason || "Aucune raison spécifiée";
			message = `👢 **${username}** (<@${userId}>) a été **expulsé** par **${executor.username}**\nRaison : ${kickReason}`;
			reason = "kick";
		} else {
			message = `👋 **${username}** (<@${userId}>) a quitté le serveur **${member.guild.name}** de lui-même.`;
		}
		
		const channel = await client.channels.fetch(targetChannelId);
		await channel.send(message);
		
		console.log(`📤 Notification envoyée : ${username} - ${reason} sur ${member.guild.name}`);
	} catch (error) {
		console.error("Erreur notification départ:", error.message);
	}
});

client.login(config.token);
