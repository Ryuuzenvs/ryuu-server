package main

import (
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"os/exec"

	"github.com/creack/pty"
	"github.com/gorilla/websocket"
)

var upgrader = websocket.Upgrader{
	CheckOrigin: func(r *http.Request) bool { return true },
}

func handleTerminalWS(w http.ResponseWriter, r *http.Request) {
	// Upgrade koneksi HTTP ke WebSocket
	ws, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		log.Println("Error upgrade websocket terminal:", err)
		return
	}
	defer ws.Close()

	log.Println("⚡ Client terhubung ke Web Terminal!")

	// 1. Spawn /bin/bash process dengan PTY (Pseudo-Terminal)
	cmd := exec.Command("/bin/bash")
	cmd.Env = append(os.Environ(), "TERM=xterm-256color")

	ptmx, err := pty.Start(cmd)
	if err != nil {
		log.Println("Gagal spawn PTY bash:", err)
		return
	}
	defer func() {
		_ = ptmx.Close()
		_ = cmd.Process.Kill()
	}()

	// 2. Stream PTY Output -> WebSocket Client (Read dari bash, kirim ke browser)
	go func() {
		buf := make([]byte, 1024)
		for {
			n, err := ptmx.Read(buf)
			if err != nil {
				if err != io.EOF {
					log.Println("Error read PTY:", err)
				}
				return
			}
			err = ws.WriteMessage(websocket.TextMessage, buf[:n])
			if err != nil {
				return
			}
		}
	}()

	// 3. Stream WebSocket Input -> PTY Input (Read dari browser, ketik ke bash)
	for {
		_, msg, err := ws.ReadMessage()
		if err != nil {
			log.Println("Terminal client disconnect")
			break
		}
		_, err = ptmx.Write(msg)
		if err != nil {
			break
		}
	}
}

func main() {
	http.HandleFunc("/ws/terminal", handleTerminalWS)
	port := ":8082"
	fmt.Println("💻 Go Interactive Terminal PTY running di port", port)
	log.Fatal(http.ListenAndServe(port, nil))
}
