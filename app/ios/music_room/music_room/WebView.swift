//
//  WebView.swift
//  music_room
//
//  Created by arthur on 21/10/2020.
//

import Foundation
import SwiftUI
import WebKit

struct WebView: UIViewRepresentable {
    
    var url: String
    
    func makeUIView(context: Context) -> WKWebView {
        
        let configuration = WKWebViewConfiguration()
        if #available(iOS 10.0, *) {
            configuration.applicationNameForUserAgent = "Version/8.0.2 Safari/600.2.5"
            configuration.allowsInlineMediaPlayback = true
            configuration.mediaTypesRequiringUserActionForPlayback = []
        }
        
        guard let url = URL(string: self.url) else {
            return WKWebView(frame: .zero, configuration: configuration)
        }
        
        let request = URLRequest(url: url)
        
        let wkWebview = WKWebView(frame: .zero, configuration: configuration)
        wkWebview.load(request)
        return wkWebview
    }
    
    func updateUIView(_ uiView: WKWebView, context: UIViewRepresentableContext<WebView>) {
    }
}
