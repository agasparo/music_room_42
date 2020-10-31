//
//  ContentView.swift
//  music_room
//
//  Created by arthur on 21/10/2020.
//

import SwiftUI

struct ContentView: View {
    var body: some View {
        WebView(url: "http://lvh.me:80/music_room/")
    }
}

struct ContentView_Previews: PreviewProvider {
    static var previews: some View {
        ContentView()
    }
}
